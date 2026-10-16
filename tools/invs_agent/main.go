package main

import (
	"flag"
	"fmt"
	"io"
	"log"
	"os"
	"path/filepath"

	"invs_agent/api"
	"invs_agent/config"
	"invs_agent/database"
	"invs_agent/exporter"
	"invs_agent/gui"
	"invs_agent/scheduler"
	"invs_agent/telegram"
	"invs_agent/tray"
)

func main() {
	silentMode := flag.Bool("s", false, "Run silently in background (Task Scheduler / Automated mode)")
	syncNow := flag.Bool("sync-now", false, "Execute sync immediately via command line")
	testDB := flag.Bool("test-db", false, "Test MS SQL database connection")
	exportExcel := flag.Bool("export-excel", false, "Extract and export datasets to Excel")
	daemonMode := flag.Bool("daemon", false, "Run in background standing daemon mode with scheduler")
	port := flag.Int("port", 18899, "Port for GUI web interface")
	flag.Parse()

	// Load Configuration
	cfg, err := config.LoadConfig()
	if err != nil {
		log.Printf("[Warning] Load config error: %v, using default settings", err)
		cfg = config.GetDefaultConfig()
	}

	// Setup logging to file if silent mode or daemon
	setupLogging(*silentMode || *daemonMode)

	// Mode 1: Test DB Connection
	if *testDB {
		fmt.Printf("Testing connection to MS SQL Server (%s:%d/%s)...\n",
			cfg.Database.Host, cfg.Database.Port, cfg.Database.Database)
		if err := database.TestConnection(cfg.Database); err != nil {
			fmt.Printf("[FAILED] %v\n", err)
			os.Exit(1)
		}
		fmt.Println("[SUCCESS] Connected to MS SQL Server successfully!")
		return
	}

	// Mode 2: Export Excel via CLI
	if *exportExcel {
		fmt.Println("Extracting datasets and generating Excel files...")
		files, err := runExportExcel(cfg)
		if err != nil {
			fmt.Printf("[FAILED] %v\n", err)
			os.Exit(1)
		}
		fmt.Printf("[SUCCESS] Generated %d Excel files:\n", len(files))
		for _, f := range files {
			fmt.Printf(" - %s\n", f)
		}
		return
	}

	// Mode 3: Silent Mode (-s) or Sync Now
	if *silentMode || *syncNow {
		log.Printf("[INVS Agent] Starting automated sync for %s (%s) Period: %04d%02d...",
			cfg.HospName, cfg.HospCode, cfg.Year, cfg.Month)
		if err := runFullSync(cfg); err != nil {
			log.Printf("[FAILED] Sync error: %v", err)
			_ = telegram.SendSyncNotification(cfg, false, err.Error())
			os.Exit(1)
		}
		log.Printf("[SUCCESS] Sync completed successfully.")
		_ = telegram.SendSyncNotification(cfg, true, "ซิงค์ข้อมูลเข้า SmartData สำเร็จเรียบร้อย")
		return
	}

	// Mode 4: Standing Daemon Mode
	if *daemonMode {
		log.Printf("[INVS Agent] Running in background daemon mode. Schedule: %s daily.", cfg.Schedule.DailyTime)
		scheduler.StartScheduler(func() error {
			return runFullSync(config.GetConfig())
		})
		// Block forever
		select {}
	}

	// Mode 5: Default GUI Mode (Interactive Desktop Application with System Tray)
	// Start scheduler in background if enabled
	if cfg.Schedule.Enabled {
		scheduler.StartScheduler(func() error {
			return runFullSync(config.GetConfig())
		})
	}

	appURL := gui.GetAppURL(*port)

	// Start GUI HTTP server in background goroutine
	go func() {
		if err := gui.StartGUIServer(*port); err != nil {
			log.Fatalf("Fatal GUI Server error: %v", err)
		}
	}()

	// Run System Tray loop on main thread (keeps process running in bottom-right tray)
	tray.StartTray(*port, tray.TrayCallbacks{
		OnOpenGUI: func() {
			gui.OpenBrowserApp(appURL)
		},
		OnSyncNow: func() {
			_ = runFullSync(config.GetConfig())
		},
		OnExportExcel: func() {
			_, _ = runExportExcel(config.GetConfig())
		},
		OnQuit: func() {
			scheduler.StopScheduler()
			os.Exit(0)
		},
	})

	// Keep running in background even if tray loop finishes without quit
	select {}
}

func setupLogging(fileLog bool) {
	log.SetFlags(log.Ldate | log.Ltime | log.Lshortfile)
	if !fileLog {
		return
	}
	exePath, err := os.Executable()
	var logPath string
	if err == nil {
		logPath = filepath.Join(filepath.Dir(exePath), "invs_agent.log")
	} else {
		logPath = "invs_agent.log"
	}

	f, err := os.OpenFile(logPath, os.O_RDWR|os.O_CREATE|os.O_APPEND, 0666)
	if err == nil {
		mw := io.MultiWriter(os.Stdout, f)
		log.SetOutput(mw)
	}
}

func runExportExcel(cfg *config.AppConfig) ([]string, error) {
	selected := []string{"druglist", "receipt", "inventory", "distribution", "purchaseplan"}
	var generated []string
	for _, fType := range selected {
		rows, err := database.ExtractDataset(cfg, fType)
		if err != nil {
			return nil, fmt.Errorf("extract %s: %w", fType, err)
		}
		fPath, err := exporter.GenerateExcel(cfg, fType, rows)
		if err != nil {
			return nil, fmt.Errorf("excel %s: %w", fType, err)
		}
		generated = append(generated, fPath)
	}
	return generated, nil
}

func runFullSync(cfg *config.AppConfig) error {
	selected := []string{"druglist", "receipt", "inventory", "distribution", "purchaseplan"}
	datasets := make(map[string][]map[string]interface{})
	var generatedExcels []string

	for _, fType := range selected {
		rows, err := database.ExtractDataset(cfg, fType)
		if err != nil {
			return fmt.Errorf("extract %s: %w", fType, err)
		}
		datasets[fType] = rows

		if cfg.Api.OutputType == "excel" {
			fPath, err := exporter.GenerateExcel(cfg, fType, rows)
			if err == nil {
				generatedExcels = append(generatedExcels, fPath)
			}
		}
	}

	if len(generatedExcels) > 0 {
		_, err := api.UploadFiles(cfg, generatedExcels)
		return err
	}

	_, err := api.UploadDatasets(cfg, datasets)
	return err
}
