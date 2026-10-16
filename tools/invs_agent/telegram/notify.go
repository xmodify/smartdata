package telegram

import (
	"encoding/json"
	"fmt"
	"net/http"
	"net/url"
	"strings"
	"time"

	"invs_agent/config"
)

// SendMessage sends a text message to Telegram chat
func SendMessage(botToken, chatID, text string) error {
	if botToken == "" || chatID == "" {
		return fmt.Errorf("bot token or chat id is empty")
	}

	apiURL := fmt.Sprintf("https://api.telegram.org/bot%s/sendMessage", strings.TrimSpace(botToken))
	data := url.Values{}
	data.Set("chat_id", strings.TrimSpace(chatID))
	data.Set("text", text)
	data.Set("parse_mode", "HTML")

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.PostForm(apiURL, data)
	if err != nil {
		return fmt.Errorf("telegram post failed: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		var errResp struct {
			Description string `json:"description"`
		}
		_ = json.NewDecoder(resp.Body).Decode(&errResp)
		return fmt.Errorf("telegram error (HTTP %d): %s", resp.StatusCode, errResp.Description)
	}

	return nil
}

// SendSyncNotification formats and sends a Telegram alert
func SendSyncNotification(cfg *config.AppConfig, success bool, details string) error {
	if !cfg.Telegram.Enabled || cfg.Telegram.ChatID == "" {
		return nil
	}

	var statusEmoji, title string
	if success {
		statusEmoji = "✅"
		title = "<b>ส่งข้อมูล 5 แฟ้มสำเร็จ (MOPH INVS)</b>"
	} else {
		statusEmoji = "❌"
		title = "<b>การส่งข้อมูล 5 แฟ้มล้มเหลว</b>"
	}

	msg := fmt.Sprintf("%s %s\n"+
		"🏥 <b>รพ.:</b> %s (%s)\n"+
		"📅 <b>งวด:</b> %02d/%04d (คลัง: %s)\n"+
		"⏰ <b>เวลา:</b> %s\n\n"+
		"📝 <b>รายละเอียด:</b>\n%s",
		statusEmoji, title,
		cfg.HospName, cfg.HospCode,
		cfg.Month, cfg.Year, cfg.StockID,
		time.Now().Format("02/01/2006 15:04:05"),
		details,
	)

	return SendMessage(cfg.Telegram.BotToken, cfg.Telegram.ChatID, msg)
}
