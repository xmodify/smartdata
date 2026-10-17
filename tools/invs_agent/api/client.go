package api

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"mime/multipart"
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"time"

	"invs_agent/config"
)

type UploadResponse struct {
	Success bool                   `json:"success"`
	Message string                 `json:"message"`
	Counts  map[string]int         `json:"counts"`
	Results map[string]interface{} `json:"results"`
	Errors  []string               `json:"errors"`
}

type TargetUploadResult struct {
	Target  config.ApiTarget `json:"target"`
	Success bool             `json:"success"`
	Message string           `json:"message"`
	Error   string           `json:"error,omitempty"`
}

func applyAuthHeaders(req *http.Request, token string) {
	token = strings.TrimSpace(token)
	if token == "" {
		return
	}
	cleanToken := strings.TrimPrefix(token, "Bearer ")
	cleanToken = strings.TrimPrefix(cleanToken, "bearer ")
	req.Header.Set("Authorization", "Bearer "+cleanToken)
	req.Header.Set("X-DMSIC-Token", cleanToken)
}

// UploadFilesToTarget sends generated Excel files via multipart/form-data to a specific target
func UploadFilesToTarget(target config.ApiTarget, cfg *config.AppConfig, filePaths []string) (*UploadResponse, error) {
	if len(filePaths) == 0 {
		return nil, fmt.Errorf("ไม่มีไฟล์ Excel ที่พร้อมส่ง")
	}

	body := &bytes.Buffer{}
	writer := multipart.NewWriter(body)

	for _, fPath := range filePaths {
		file, err := os.Open(fPath)
		if err != nil {
			return nil, fmt.Errorf("open file %s error: %w", fPath, err)
		}
		part, err := writer.CreateFormFile("files[]", filepath.Base(fPath))
		if err != nil {
			file.Close()
			return nil, err
		}
		_, err = io.Copy(part, file)
		file.Close()
		if err != nil {
			return nil, err
		}
	}

	err := writer.Close()
	if err != nil {
		return nil, err
	}

	targetURL := strings.TrimSpace(target.Url)
	if targetURL == "" {
		targetURL = strings.TrimSpace(cfg.Api.Url)
	}

	req, err := http.NewRequest("POST", targetURL, body)
	if err != nil {
		return nil, err
	}

	req.Header.Set("Content-Type", writer.FormDataContentType())
	req.Header.Set("User-Agent", "SmartData-INVS-Agent/1.0.0 (Windows; Go)")
	req.Header.Set("Accept", "application/json")
	applyAuthHeaders(req, target.Token)

	client := &http.Client{Timeout: 90 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("HTTP request failed: %w", err)
	}
	defer resp.Body.Close()

	respBody, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("read response failed: %w", err)
	}

	if resp.StatusCode >= 400 {
		return nil, fmt.Errorf("server returned HTTP %d: %s", resp.StatusCode, string(respBody))
	}

	var uploadResp UploadResponse
	if err := json.Unmarshal(respBody, &uploadResp); err != nil {
		snippet := strings.TrimSpace(string(respBody))
		if len(snippet) > 200 {
			snippet = snippet[:200] + "..."
		}
		return nil, fmt.Errorf("server did not return valid JSON. Response: %s", snippet)
	}

	if !uploadResp.Success {
		return &uploadResp, fmt.Errorf("API error: %s", uploadResp.Message)
	}

	return &uploadResp, nil
}

// UploadDatasetsToTarget sends JSON datasets directly to a specific target
func UploadDatasetsToTarget(target config.ApiTarget, cfg *config.AppConfig, datasets map[string][]map[string]interface{}) (*UploadResponse, error) {
	yearAD := cfg.Year
	if yearAD > 2400 {
		yearAD -= 543
	}
	periodStr := fmt.Sprintf("%04d%02d", yearAD, cfg.Month)

	payload := map[string]interface{}{
		"dataset":    datasets,
		"datasets":   datasets,
		"hosp_code":  cfg.HospCode,
		"period":     periodStr,
		"period_rpt": periodStr,
	}

	data, err := json.Marshal(payload)
	if err != nil {
		return nil, err
	}

	targetURL := strings.TrimSpace(target.Url)
	if targetURL == "" {
		targetURL = strings.TrimSpace(cfg.Api.Url)
	}

	req, err := http.NewRequest("POST", targetURL, bytes.NewBuffer(data))
	if err != nil {
		return nil, err
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("User-Agent", "SmartData-INVS-Agent/1.0.0 (Windows; Go)")
	req.Header.Set("Accept", "application/json")
	applyAuthHeaders(req, target.Token)

	client := &http.Client{Timeout: 90 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("HTTP request failed: %w", err)
	}
	defer resp.Body.Close()

	respBody, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("read response failed: %w", err)
	}

	if resp.StatusCode >= 400 {
		return nil, fmt.Errorf("server returned HTTP %d: %s", resp.StatusCode, string(respBody))
	}

	var uploadResp UploadResponse
	if err := json.Unmarshal(respBody, &uploadResp); err != nil {
		snippet := strings.TrimSpace(string(respBody))
		if len(snippet) > 200 {
			snippet = snippet[:200] + "..."
		}
		return nil, fmt.Errorf("server did not return valid JSON. Response: %s", snippet)
	}

	if !uploadResp.Success {
		return &uploadResp, fmt.Errorf("API error: %s", uploadResp.Message)
	}

	return &uploadResp, nil
}

// UploadToTarget dispatches upload based on target or global settings
func UploadToTarget(target config.ApiTarget, cfg *config.AppConfig, datasets map[string][]map[string]interface{}, excelFiles []string) (*UploadResponse, error) {
	outType := target.OutputType
	if outType == "" {
		outType = cfg.Api.OutputType
	}

	if outType == "excel" && len(excelFiles) > 0 {
		return UploadFilesToTarget(target, cfg, excelFiles)
	}
	return UploadDatasetsToTarget(target, cfg, datasets)
}

// UploadToAllTargets sends data to all enabled targets and returns per-target results
func UploadToAllTargets(cfg *config.AppConfig, datasets map[string][]map[string]interface{}, excelFiles []string) []TargetUploadResult {
	targets := cfg.GetActiveApiTargets()
	if len(targets) == 0 {
		return []TargetUploadResult{
			{
				Success: false,
				Message: "ไม่มี Server ปลายทางที่เปิดใช้งาน",
				Error:   "ไม่มี Server ปลายทางที่เปิดใช้งาน (กรุณาตั้งค่า URL และเปิดใช้งานในแท็บตั้งค่า API)",
			},
		}
	}

	var results []TargetUploadResult
	for _, target := range targets {
		resp, err := UploadToTarget(target, cfg, datasets, excelFiles)
		res := TargetUploadResult{
			Target: target,
		}
		if err != nil {
			res.Success = false
			res.Error = err.Error()
			res.Message = fmt.Sprintf("ส่งไปยัง %s ล้มเหลว: %v", target.Name, err)
		} else {
			res.Success = true
			res.Message = resp.Message
			if res.Message == "" {
				res.Message = "ส่งข้อมูลสำเร็จเรียบร้อย"
			}
		}
		results = append(results, res)
	}
	return results
}

// UploadFiles sends generated Excel files (Legacy backward compatibility)
func UploadFiles(cfg *config.AppConfig, filePaths []string) (*UploadResponse, error) {
	target := config.ApiTarget{
		Name:  "Primary",
		Url:   cfg.Api.Url,
		Token: cfg.Api.Token,
	}
	return UploadFilesToTarget(target, cfg, filePaths)
}

// UploadDatasets sends JSON datasets directly (Legacy backward compatibility)
func UploadDatasets(cfg *config.AppConfig, datasets map[string][]map[string]interface{}) (*UploadResponse, error) {
	target := config.ApiTarget{
		Name:  "Primary",
		Url:   cfg.Api.Url,
		Token: cfg.Api.Token,
	}
	return UploadDatasetsToTarget(target, cfg, datasets)
}

// TestApiConnection checks health endpoint or connectivity of API
func TestApiConnection(apiUrl, token string) error {
	apiUrl = strings.TrimSpace(apiUrl)
	token = strings.TrimSpace(token)
	if apiUrl == "" {
		return fmt.Errorf("กรุณาระบุ API URL ปลายทาง")
	}
	if token == "" {
		return fmt.Errorf("กรุณากรอก Bearer API Token หรือ DMSIC Token ก่อนกดทดสอบ (ยังไม่ได้ระบุ Token)")
	}

	if !strings.HasPrefix(apiUrl, "http://") && !strings.HasPrefix(apiUrl, "https://") {
		return fmt.Errorf("รูปแบบ API URL ไม่ถูกต้อง (ต้องขึ้นต้นด้วย http:// หรือ https://)")
	}

	client := &http.Client{Timeout: 10 * time.Second}

	// 1. Try health check endpoint if URL ends with /upload
	healthUrl := apiUrl
	if strings.HasSuffix(healthUrl, "/upload") {
		healthUrl = strings.TrimSuffix(healthUrl, "/upload") + "/health"
	}

	req, err := http.NewRequest("GET", healthUrl, nil)
	if err != nil {
		return err
	}
	req.Header.Set("User-Agent", "SmartData-INVS-Agent/1.0.0 (Windows; Go)")
	req.Header.Set("Accept", "application/json")
	applyAuthHeaders(req, token)

	resp, err := client.Do(req)
	if err != nil {
		return fmt.Errorf("ไม่สามารถติดต่อ Server ปลายทางได้: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode == http.StatusOK {
		return nil
	}
	if resp.StatusCode == http.StatusUnauthorized {
		return fmt.Errorf("API Token ไม่ถูกต้อง (HTTP 401 Unauthorized): กรุณาตรวจสอบ Bearer API Token / DMSIC Token")
	}
	if resp.StatusCode == http.StatusForbidden {
		return fmt.Errorf("ไม่มีสิทธิ์เข้าถึง API (HTTP 403 Forbidden)")
	}

	// 2. If /health was 404 (e.g. DMSIC or custom endpoint without /health), test against upload URL directly
	if resp.StatusCode == http.StatusNotFound && healthUrl != apiUrl {
		pingBody := strings.NewReader(`{"test_connection":true,"ping":true}`)
		postReq, pErr := http.NewRequest("POST", apiUrl, pingBody)
		if pErr != nil {
			return pErr
		}
		postReq.Header.Set("User-Agent", "SmartData-INVS-Agent/1.0.0 (Windows; Go)")
		postReq.Header.Set("Content-Type", "application/json")
		postReq.Header.Set("Accept", "application/json")
		applyAuthHeaders(postReq, token)

		postResp, pErr2 := client.Do(postReq)
		if pErr2 != nil {
			return fmt.Errorf("ไม่สามารถเชื่อมต่อไปยัง URL ได้: %w", pErr2)
		}
		defer postResp.Body.Close()

		if postResp.StatusCode == http.StatusUnauthorized {
			return fmt.Errorf("API Token ไม่ถูกต้อง (HTTP 401 Unauthorized): กรุณาตรวจสอบ Token")
		}
		if postResp.StatusCode == http.StatusForbidden {
			return fmt.Errorf("ไม่มีสิทธิ์เข้าถึง API (HTTP 403 Forbidden)")
		}
		if postResp.StatusCode == http.StatusNotFound {
			return fmt.Errorf("ไม่พบ API Endpoint ปลายทาง (HTTP 404 Not Found): กรุณาตรวจสอบ URL ให้ถูกต้อง")
		}
		if postResp.StatusCode == http.StatusMethodNotAllowed {
			return fmt.Errorf("API ไม่รองรับ Method นี้ (HTTP 405 Method Not Allowed): กรุณาตรวจสอบ URL")
		}
		if postResp.StatusCode >= 500 {
			return fmt.Errorf("Server ปลายทางเกิดข้อผิดพลาด (HTTP %d)", postResp.StatusCode)
		}

		// Status 200, 204 are successful
		if postResp.StatusCode == http.StatusOK || postResp.StatusCode == http.StatusNoContent {
			return nil
		}
		// In 400 or 422: verify it wasn't auth failure
		if postResp.StatusCode == http.StatusBadRequest || postResp.StatusCode == http.StatusUnprocessableEntity {
			b, _ := io.ReadAll(postResp.Body)
			bodyStr := strings.ToLower(string(b))
			if strings.Contains(bodyStr, "unauthorized") || strings.Contains(bodyStr, "token") || strings.Contains(bodyStr, "auth") {
				return fmt.Errorf("API Token ไม่ถูกต้อง (HTTP %d): %s", postResp.StatusCode, string(b))
			}
			return nil
		}
	}

	return fmt.Errorf("API endpoint ตอบกลับด้วย HTTP %d (%s)", resp.StatusCode, resp.Status)
}
