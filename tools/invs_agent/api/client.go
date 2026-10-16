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

// UploadFiles sends generated Excel files via multipart/form-data
func UploadFiles(cfg *config.AppConfig, filePaths []string) (*UploadResponse, error) {
	if len(filePaths) == 0 {
		return nil, fmt.Errorf("no files to upload")
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

	req, err := http.NewRequest("POST", cfg.Api.Url, body)
	if err != nil {
		return nil, err
	}

	req.Header.Set("Content-Type", writer.FormDataContentType())
	req.Header.Set("User-Agent", "SmartData-INVS-Agent/1.0.0 (Windows; Go)")
	req.Header.Set("Accept", "application/json")
	if cfg.Api.Token != "" {
		req.Header.Set("Authorization", "Bearer "+strings.TrimSpace(cfg.Api.Token))
		req.Header.Set("X-DMSIC-Token", strings.TrimSpace(cfg.Api.Token))
	}

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
		return nil, fmt.Errorf("server did not return valid JSON (check API URL). Response: %s", snippet)
	}

	if !uploadResp.Success {
		return &uploadResp, fmt.Errorf("API error: %s", uploadResp.Message)
	}

	return &uploadResp, nil
}

// UploadDatasets sends JSON datasets directly to SmartData API
func UploadDatasets(cfg *config.AppConfig, datasets map[string][]map[string]interface{}) (*UploadResponse, error) {
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

	req, err := http.NewRequest("POST", cfg.Api.Url, bytes.NewBuffer(data))
	if err != nil {
		return nil, err
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("User-Agent", "SmartData-INVS-Agent/1.0.0 (Windows; Go)")
	req.Header.Set("Accept", "application/json")
	if cfg.Api.Token != "" {
		req.Header.Set("Authorization", "Bearer "+strings.TrimSpace(cfg.Api.Token))
		req.Header.Set("X-DMSIC-Token", strings.TrimSpace(cfg.Api.Token))
	}

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
		return nil, fmt.Errorf("server did not return valid JSON (check API URL). Response: %s", snippet)
	}

	if !uploadResp.Success {
		return &uploadResp, fmt.Errorf("API error: %s", uploadResp.Message)
	}

	return &uploadResp, nil
}

// TestApiConnection checks health endpoint of API
func TestApiConnection(apiUrl, token string) error {
	healthUrl := apiUrl
	if strings.HasSuffix(healthUrl, "/upload") {
		healthUrl = strings.TrimSuffix(healthUrl, "/upload") + "/health"
	}

	req, err := http.NewRequest("GET", healthUrl, nil)
	if err != nil {
		return err
	}
	req.Header.Set("User-Agent", "SmartData-INVS-Agent/1.0.0")
	if token != "" {
		req.Header.Set("Authorization", "Bearer "+strings.TrimSpace(token))
	}

	client := &http.Client{Timeout: 8 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return fmt.Errorf("cannot reach API URL: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode >= 400 && resp.StatusCode != http.StatusUnauthorized {
		return fmt.Errorf("API endpoint returned HTTP %d", resp.StatusCode)
	}

	return nil
}
