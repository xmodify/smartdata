package config

import (
	"encoding/json"
	"os"
	"path/filepath"
	"sync"
)

type FileSelection struct {
	SelectAll           bool   `json:"select_all"`
	Druglist            bool   `json:"druglist"`
	DruglistOnlyChanged bool   `json:"druglist_only_changed"`
	Purchaseplan        bool   `json:"purchaseplan"`
	Receipt             bool   `json:"receipt"`
	Distribution        bool   `json:"distribution"`
	Inventory           bool   `json:"inventory"`
	InventoryDate       string `json:"inventory_date"` // DD/MM/YYYY or YYYYMMDD
}

type DatabaseConfig struct {
	Host                   string `json:"host"`
	Port                   int    `json:"port"`
	Database               string `json:"database"`
	Username               string `json:"username"`
	Password               string `json:"password"`
	Encrypt                bool   `json:"encrypt"`
	TrustServerCertificate bool   `json:"trust_server_certificate"`
}

type ApiConfig struct {
	Url        string `json:"url"`
	Token      string `json:"token"`
	OutputType string `json:"output_type"` // "text" or "excel"
}

type ScheduleConfig struct {
	Enabled       bool   `json:"enabled"`
	DailyTime     string `json:"daily_time"`     // e.g. "01:00"
	IntervalHours int    `json:"interval_hours"` // e.g. 0 (disabled), 6, 12, 24
}

type TelegramConfig struct {
	Enabled  bool   `json:"enabled"`
	BotToken string `json:"bot_token"`
	ChatID   string `json:"chat_id"`
}

type QueriesConfig struct {
	Druglist     string `json:"druglist"`
	Purchaseplan string `json:"purchaseplan"`
	Receipt      string `json:"receipt"`
	Distribution string `json:"distribution"`
	Inventory    string `json:"inventory"`
}

type AppConfig struct {
	HospCode      string         `json:"hosp_code"`
	HospName      string         `json:"hosp_name"`
	Year          int            `json:"year"`  // e.g. 2569 or 2026
	Month         int            `json:"month"` // 1 - 12
	StockID       string         `json:"stock_id"`
	AccountType   string         `json:"account_type"` // e.g. "ED (ยาในบัญชียาหลัก);NED (ยานอกบัญชียาหลัก);CM"
	Selection     FileSelection  `json:"selection"`
	Database      DatabaseConfig `json:"database"`
	Api           ApiConfig      `json:"api"`
	Schedule      ScheduleConfig `json:"schedule"`
	Telegram      TelegramConfig `json:"telegram"`
	OutputDir     string         `json:"output_dir"`
	Queries       QueriesConfig  `json:"queries"`
}

var (
	currentConfig *AppConfig
	configMutex   sync.RWMutex
	configPath    string
)

func init() {
	exePath, err := os.Executable()
	if err != nil {
		configPath = "config.json"
	} else {
		configPath = filepath.Join(filepath.Dir(exePath), "config.json")
	}
}

// GetDefaultConfig returns standard default configurations
func GetDefaultConfig() *AppConfig {
	return &AppConfig{
		HospCode:    "EA0010989",
		HospName:    "โรงพยาบาลหัวตะพาน",
		Year:        2569,
		Month:       10,
		StockID:     "STOCK1",
		AccountType: "ED (ยาในบัญชียาหลัก);NED (ยานอกบัญชียาหลัก);CM",
		Selection: FileSelection{
			SelectAll:           true,
			Druglist:            true,
			DruglistOnlyChanged: false,
			Purchaseplan:        true,
			Receipt:             true,
			Distribution:        true,
			Inventory:           true,
			InventoryDate:       "16/10/2569",
		},
		Database: DatabaseConfig{
			Host:                   "127.0.0.1",
			Port:                   1433,
			Database:               "INVS_DB",
			Username:               "sa",
			Password:               "",
			Encrypt:                false,
			TrustServerCertificate: true,
		},
		Api: ApiConfig{
			Url:        "http://127.0.0.1/smartdata/api/v1/moph-f5/upload",
			Token:      "",
			OutputType: "text",
		},
		Schedule: ScheduleConfig{
			Enabled:       false,
			DailyTime:     "01:00",
			IntervalHours: 0,
		},
		Telegram: TelegramConfig{
			Enabled:  false,
			BotToken: "",
			ChatID:   "",
		},
		OutputDir: "./exports",
		Queries: QueriesConfig{
			Druglist: `SELECT 
    @HospCode AS HOSP_CODE,
    d.working_code AS WORKING_CODE,
    d.generic_name AS GENERIC_NAME,
    ISNULL(d.gpuid, '') AS GPUID,
    ISNULL(d.nlem, 'E') AS NLEM,
    ISNULL(d.product_cat, '1') AS PRODUCT_CAT,
    ISNULL(d.base_unit, 'tab') AS BASE_UNIT,
    ISNULL(d.status, '1') AS STATUS,
    CONVERT(VARCHAR(8), ISNULL(d.date_status, GETDATE()), 112) AS DATE_STATUS,
    @PeriodRpt AS PERIOD_RPT,
    CONVERT(VARCHAR(8), GETDATE(), 112) AS DATE_SEND
FROM inv_drug d
WHERE d.stock_id = @StockID`,
			Purchaseplan: `SELECT 
    @HospCode AS HOSP_CODE,
    ISNULL(p.yearbudget, @YearBudget) AS YEARBUDGET,
    p.working_code AS WORKING_CODE,
    p.generic_name AS GENERIC_NAME,
    ISNULL(p.gpuid, '') AS GPUID,
    ISNULL(p.nlem, 'E') AS NLEM,
    ISNULL(p.qty_use_year3, 0) AS QTY_USE_YEAR3,
    ISNULL(p.qty_use_year2, 0) AS QTY_USE_YEAR2,
    ISNULL(p.qty_use_year1, 0) AS QTY_USE_YEAR1,
    ISNULL(p.qty_this_year, 0) AS QTY_THIS_YEAR,
    ISNULL(p.pack_size, 1) AS PACK_SIZE,
    ISNULL(p.base_unit, 'tab') AS BASE_UNIT,
    ISNULL(p.pack_cost, 0.0) AS PACK_COST,
    ISNULL(p.value_this_year, 0.0) AS VALUE_THIS_YEAR,
    ISNULL(p.qty_plan_trimes1, 0) AS QTY_PLAN_TRIMES1,
    ISNULL(p.qty_plan_trimes2, 0) AS QTY_PLAN_TRIMES2,
    ISNULL(p.qty_plan_trimes3, 0) AS QTY_PLAN_TRIMES3,
    ISNULL(p.qty_plan_trimes4, 0) AS QTY_PLAN_TRIMES4,
    @PeriodRpt AS PERIOD_RPT,
    CONVERT(VARCHAR(8), GETDATE(), 112) AS DATE_SEND
FROM inv_purchaseplan p
WHERE p.stock_id = @StockID AND p.yearbudget = @YearBudget`,
			Receipt: `SELECT 
    @HospCode AS HOSP_CODE,
    r.working_code AS WORKING_CODE,
    r.trade_name AS TRADE_NAME,
    ISNULL(r.tpuid, '') AS TPUID,
    ISNULL(r.vendor_name, '') AS VENDOR_NAME,
    ISNULL(r.vendor_tax_id, '') AS VENDOR_TAX_ID,
    ISNULL(r.qty_rcv, 0) AS QTY_RCV,
    ISNULL(r.pack_size, 1) AS PACK_SIZE,
    ISNULL(r.base_unit, 'tab') AS BASE_UNIT,
    ISNULL(r.pack_cost, 0.0) AS PACK_COST,
    ISNULL(r.total_value, 0.0) AS TOTAL_VALUE,
    ISNULL(r.lot_no, '') AS LOT_NO,
    CONVERT(VARCHAR(8), r.expire_date, 112) AS EXPIRE_DATE,
    ISNULL(r.rcv_no, '') AS RCV_NO,
    ISNULL(r.po_no, '') AS PO_NO,
    ISNULL(r.cnt_no, '') AS CNT_NO,
    CONVERT(VARCHAR(8), r.date_rcv, 112) AS DATE_RCV,
    ISNULL(r.buy_method_id, '1') AS BUY_METHOD_ID,
    ISNULL(r.co_purchase_id, '') AS CO_PURCHASE_ID,
    ISNULL(r.rcv_flag, '1') AS RCV_FLAG,
    @PeriodRpt AS PERIOD_RPT,
    CONVERT(VARCHAR(8), GETDATE(), 112) AS DATE_SEND
FROM inv_receipt r
WHERE r.stock_id = @StockID AND CONVERT(VARCHAR(6), r.date_rcv, 112) = @PeriodRpt`,
			Distribution: `SELECT 
    @HospCode AS HOSP_CODE,
    d.working_code AS WORKING_CODE,
    d.trade_name AS TRADE_NAME,
    ISNULL(d.tpuid, '') AS TPUID,
    ISNULL(d.qty_dis, 0) AS QTY_DIS,
    ISNULL(d.pack_size, 1) AS PACK_SIZE,
    ISNULL(d.base_unit, 'tab') AS BASE_UNIT,
    ISNULL(d.value, 0.0) AS VALUE,
    ISNULL(d.dis_dept_group, 'OPD') AS DIS_DEPT_GROUP,
    @PeriodRpt AS PERIOD_RPT,
    CONVERT(VARCHAR(8), GETDATE(), 112) AS DATE_SEND
FROM inv_distribution d
WHERE d.stock_id = @StockID AND d.period_rpt = @PeriodRpt`,
			Inventory: `SELECT 
    @HospCode AS HOSP_CODE,
    i.working_code AS WORKING_CODE,
    i.trade_name AS TRADE_NAME,
    ISNULL(i.tpuid, '') AS TPUID,
    ISNULL(i.vendor_name, '') AS VENDOR_NAME,
    ISNULL(i.vendor_tax_id, '') AS VENDOR_TAX_ID,
    ISNULL(i.qty_onhand, 0) AS QTY_ONHAND,
    ISNULL(i.pack_size, 1) AS PACK_SIZE,
    ISNULL(i.base_unit, 'tab') AS BASE_UNIT,
    ISNULL(i.pack_cost, 0.0) AS PACK_COST,
    ISNULL(i.value_onhand, 0.0) AS VALUE_ONHAND,
    ISNULL(i.lot_no, '') AS LOT_NO,
    CONVERT(VARCHAR(8), i.expire_date, 112) AS EXPIRE_DATE,
    @DateOnhand AS DATE_ONHAND,
    CONVERT(VARCHAR(8), GETDATE(), 112) AS DATE_SEND
FROM inv_inventory i
WHERE i.stock_id = @StockID`,
		},
	}
}

// LoadConfig reads config from config.json or initializes default
func LoadConfig() (*AppConfig, error) {
	configMutex.Lock()
	defer configMutex.Unlock()

	if _, err := os.Stat(configPath); os.IsNotExist(err) {
		currentConfig = GetDefaultConfig()
		_ = saveConfigLocked(currentConfig)
		return currentConfig, nil
	}

	data, err := os.ReadFile(configPath)
	if err != nil {
		currentConfig = GetDefaultConfig()
		return currentConfig, err
	}

	cfg := GetDefaultConfig()
	if err := json.Unmarshal(data, cfg); err != nil {
		currentConfig = GetDefaultConfig()
		return currentConfig, err
	}

	currentConfig = cfg
	return currentConfig, nil
}

// GetConfig returns the active cached config
func GetConfig() *AppConfig {
	configMutex.RLock()
	defer configMutex.RUnlock()
	if currentConfig == nil {
		cfg, _ := LoadConfig()
		return cfg
	}
	return currentConfig
}

// SaveConfig writes config to disk safely
func SaveConfig(cfg *AppConfig) error {
	configMutex.Lock()
	defer configMutex.Unlock()
	currentConfig = cfg
	return saveConfigLocked(cfg)
}

func saveConfigLocked(cfg *AppConfig) error {
	data, err := json.MarshalIndent(cfg, "", "  ")
	if err != nil {
		return err
	}
	dir := filepath.Dir(configPath)
	if dir != "" && dir != "." {
		_ = os.MkdirAll(dir, 0755)
	}
	return os.WriteFile(configPath, data, 0644)
}
