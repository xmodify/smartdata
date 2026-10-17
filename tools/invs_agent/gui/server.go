package gui

import (
	_ "embed"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"strings"
	"time"

	"invs_agent/api"
	"invs_agent/config"
	"invs_agent/database"
	"invs_agent/exporter"
	"invs_agent/telegram"
)

//go:embed index.html
var indexHTML []byte

func StartGUIServer(port int) error {
	mux := http.NewServeMux()

	// 1. Serve Embedded GUI
	mux.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/" {
			http.NotFound(w, r)
			return
		}
		w.Header().Set("Content-Type", "text/html; charset=utf-8")
		_, _ = w.Write(indexHTML)
	})

	// 2. API Config
	mux.HandleFunc("/api/config", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		if r.Method == "GET" {
			cfg := config.GetConfig()
			_ = json.NewEncoder(w).Encode(cfg)
			return
		} else if r.Method == "POST" {
			var newCfg config.AppConfig
			if err := json.NewDecoder(r.Body).Decode(&newCfg); err != nil {
				http.Error(w, err.Error(), 400)
				return
			}
			existing := config.GetConfig()
			if newCfg.Queries.Druglist == "" {
				newCfg.Queries.Druglist = existing.Queries.Druglist
			}
			if newCfg.Queries.Receipt == "" {
				newCfg.Queries.Receipt = existing.Queries.Receipt
			}
			if newCfg.Queries.Inventory == "" {
				newCfg.Queries.Inventory = existing.Queries.Inventory
			}
			if newCfg.Queries.Distribution == "" {
				newCfg.Queries.Distribution = existing.Queries.Distribution
			}
			if newCfg.Queries.Purchaseplan == "" {
				newCfg.Queries.Purchaseplan = existing.Queries.Purchaseplan
			}
			if err := config.SaveConfig(&newCfg); err != nil {
				_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": err.Error()})
				return
			}
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": true})
			return
		}
		http.Error(w, "method not allowed", 405)
	})

	// 3. Test Database Connection
	mux.HandleFunc("/api/test-db", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		var dbCfg config.DatabaseConfig
		if err := json.NewDecoder(r.Body).Decode(&dbCfg); err != nil {
			http.Error(w, err.Error(), 400)
			return
		}
		err := database.TestConnection(dbCfg)
		if err != nil {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": err.Error()})
			return
		}
		_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": true})
	})

	// 4. Test API Connection
	mux.HandleFunc("/api/test-api", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		var req struct {
			URL   string `json:"url"`
			Token string `json:"token"`
		}
		if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
			http.Error(w, err.Error(), 400)
			return
		}
		if strings.TrimSpace(req.URL) == "" {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": "กรุณากรอก API URL ปลายทางก่อนทดสอบ"})
			return
		}
		if strings.TrimSpace(req.Token) == "" {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": "กรุณากรอก Bearer API Token หรือ DMSIC Token ก่อนกดทดสอบ (ยังไม่ได้ระบุ Token)"})
			return
		}
		err := api.TestApiConnection(req.URL, req.Token)
		if err != nil {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": err.Error()})
			return
		}
		_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": true})
	})

	// 5. Test Telegram
	mux.HandleFunc("/api/test-telegram", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		var req struct {
			ChatID string `json:"chat_id"`
		}
		_ = json.NewDecoder(r.Body).Decode(&req)
		cfg := config.GetConfig()
		chatID := req.ChatID
		if chatID == "" {
			chatID = cfg.Telegram.ChatID
		}
		err := telegram.SendMessage(cfg.Telegram.BotToken, chatID, "🔔 <b>ทดสอบการเชื่อมต่อจาก INVS Agent</b>\nระบบส่งข้อมูล 5 แฟ้มยา SmartData เชื่อมต่อสำเร็จเรียบร้อย!")
		if err != nil {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": err.Error()})
			return
		}
		_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": true})
	})

	// 6. Execute Sync via API
	mux.HandleFunc("/api/sync-api", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		var cfg config.AppConfig
		if err := json.NewDecoder(r.Body).Decode(&cfg); err != nil {
			http.Error(w, err.Error(), 400)
			return
		}
		_ = config.SaveConfig(&cfg)

		selectedFiles := getSelectedFiles(&cfg)
		if len(selectedFiles) == 0 {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": "ไม่ได้เลือกแฟ้มข้อมูลที่ต้องการส่ง"})
			return
		}

		datasets := make(map[string][]map[string]interface{})
		var details []map[string]interface{}
		var generatedExcels []string

		for _, fileType := range selectedFiles {
			rows, err := database.ExtractDataset(&cfg, fileType)
			if err != nil {
				_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": fmt.Sprintf("แฟ้ม %s: %v", fileType, err)})
				return
			}
			datasets[fileType] = rows
			details = append(details, map[string]interface{}{
				"file":   fileType,
				"rows":   len(rows),
				"status": "พร้อมส่ง",
			})

			// If configured to generate Excel first
			if cfg.Api.OutputType == "excel" {
				excelPath, err := exporter.GenerateExcel(&cfg, fileType, rows)
				if err == nil {
					generatedExcels = append(generatedExcels, excelPath)
				}
			}
		}

		results := api.UploadToAllTargets(&cfg, datasets, generatedExcels)
		var errMsgs []string
		var successCount int
		for _, res := range results {
			if res.Success {
				successCount++
			} else {
				errMsgs = append(errMsgs, fmt.Sprintf("%s (%s)", res.Target.Name, res.Error))
			}
		}

		if len(results) == 0 {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": "ไม่มี Server ปลายทางที่เปิดใช้งาน"})
			return
		}

		if successCount == 0 && len(errMsgs) > 0 {
			errStr := strings.Join(errMsgs, "; ")
			_ = telegram.SendSyncNotification(&cfg, false, fmt.Sprintf("API Error: %s", errStr))
			_ = json.NewEncoder(w).Encode(map[string]interface{}{
				"success": false,
				"error":   errStr,
				"results": results,
			})
			return
		}

		summaryMsg := fmt.Sprintf("ส่งสำเร็จ %d/%d Server (%d แฟ้ม)", successCount, len(results), len(selectedFiles))
		if len(errMsgs) > 0 {
			summaryMsg += fmt.Sprintf(" (ล้มเหลว: %s)", strings.Join(errMsgs, ", "))
		}
		_ = telegram.SendSyncNotification(&cfg, true, summaryMsg)

		_ = json.NewEncoder(w).Encode(map[string]interface{}{
			"success": true,
			"details": details,
			"results": results,
			"message": summaryMsg,
		})
	})

	// 7. Execute Export Excel
	mux.HandleFunc("/api/export-excel", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		var cfg config.AppConfig
		if err := json.NewDecoder(r.Body).Decode(&cfg); err != nil {
			http.Error(w, err.Error(), 400)
			return
		}
		_ = config.SaveConfig(&cfg)

		selectedFiles := getSelectedFiles(&cfg)
		if len(selectedFiles) == 0 {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": "ไม่ได้เลือกแฟ้มข้อมูลที่ต้องการส่งออก"})
			return
		}

		var filesInfo []map[string]interface{}
		for _, fileType := range selectedFiles {
			rows, err := database.ExtractDataset(&cfg, fileType)
			if err != nil {
				_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": fmt.Sprintf("แฟ้ม %s: %v", fileType, err)})
				return
			}
			fPath, err := exporter.GenerateExcel(&cfg, fileType, rows)
			if err != nil {
				_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": fmt.Sprintf("สร้าง Excel %s ไม่สำเร็จ: %v", fileType, err)})
				return
			}
			filesInfo = append(filesInfo, map[string]interface{}{
				"filename": filepath.Base(fPath),
				"path":     fPath,
				"rows":     len(rows),
			})
		}

		absOutputDir, _ := filepath.Abs(cfg.OutputDir)
		_ = json.NewEncoder(w).Encode(map[string]interface{}{
			"success":    true,
			"files":      filesInfo,
			"output_dir": absOutputDir,
		})
	})

	// 8. Choose Folder Dialog (Popup เลือกโฟลเดอร์สำหรับบันทึกไฟล์ Excel)
	mux.HandleFunc("/api/choose-folder", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		folder, err := ChooseFolderDialog("เลือกโฟลเดอร์สำหรับบันทึกไฟล์ Excel (5 แฟ้มยา)")
		if err != nil {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "error": err.Error()})
			return
		}
		if folder == "" {
			_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "cancelled": true})
			return
		}
		_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "path": folder})
	})

	// 9. Open Folder in Explorer
	mux.HandleFunc("/api/open-folder", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		folder := r.URL.Query().Get("folder")
		if folder == "" {
			cfg := config.GetConfig()
			folder = cfg.OutputDir
		}
		if folder == "" {
			folder = "./exports"
		}
		absFolder, err := filepath.Abs(folder)
		if err != nil {
			absFolder = folder
		}
		_ = os.MkdirAll(absFolder, 0755)
		if runtime.GOOS == "windows" {
			exec.Command("explorer.exe", absFolder).Start()
		} else {
			exec.Command("xdg-open", absFolder).Start()
		}
		_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "path": absFolder})
	})

	// 10. Shutdown API
	mux.HandleFunc("/api/quit", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		_ = json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "message": "กำลังปิดโปรเซส INVS Agent..."})
		go func() {
			time.Sleep(500 * time.Millisecond)
			os.Exit(0)
		}()
	})

	addr := fmt.Sprintf("127.0.0.1:%d", port)
	server := &http.Server{
		Addr:    addr,
		Handler: mux,
	}

	appURL := fmt.Sprintf("http://%s", addr)
	go OpenBrowserApp(appURL)

	log.Printf("[GUI] INVS Agent Web Interface running at %s", appURL)
	return server.ListenAndServe()
}

func getSelectedFiles(cfg *config.AppConfig) []string {
	var files []string
	if cfg.Selection.SelectAll {
		return []string{"druglist", "receipt", "inventory", "distribution", "purchaseplan"}
	}
	if cfg.Selection.Druglist {
		files = append(files, "druglist")
	}
	if cfg.Selection.Receipt {
		files = append(files, "receipt")
	}
	if cfg.Selection.Inventory {
		files = append(files, "inventory")
	}
	if cfg.Selection.Distribution {
		files = append(files, "distribution")
	}
	if cfg.Selection.Purchaseplan {
		files = append(files, "purchaseplan")
	}
	return files
}

func GetAppURL(port int) string {
	return fmt.Sprintf("http://127.0.0.1:%d", port)
}

// OpenBrowserApp attempts to launch in Chrome/Edge app mode or system browser
func OpenBrowserApp(url string) {
	if runtime.GOOS != "windows" {
		_ = exec.Command("open", url).Start()
		return
	}

	// Try Microsoft Edge App Mode (native look)
	edgePaths := []string{
		`C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe`,
		`C:\Program Files\Microsoft\Edge\Application\msedge.exe`,
	}
	for _, p := range edgePaths {
		if _, err := os.Stat(p); err == nil {
			cmd := exec.Command(p, fmt.Sprintf("--app=%s", url), "--window-size=950,780")
			if err := cmd.Start(); err == nil {
				return
			}
		}
	}

	// Try Google Chrome App Mode
	chromePaths := []string{
		`C:\Program Files\Google\Chrome\Application\chrome.exe`,
		`C:\Program Files (x86)\Google\Chrome\Application\chrome.exe`,
	}
	for _, p := range chromePaths {
		if _, err := os.Stat(p); err == nil {
			cmd := exec.Command(p, fmt.Sprintf("--app=%s", url), "--window-size=950,780")
			if err := cmd.Start(); err == nil {
				return
			}
		}
	}

	// Fallback to default browser
	cmd := exec.Command("cmd", "/c", "start", strings.ReplaceAll(url, "&", "^&"))
	_ = cmd.Start()
}
