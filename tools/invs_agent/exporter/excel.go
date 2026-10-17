package exporter

import (
	"fmt"
	"os"
	"path/filepath"
	"time"

	"github.com/xuri/excelize/v2"
	"invs_agent/config"
)

var FileColumns = map[string][]string{
	"druglist": {
		"HOSP_CODE", "WORKING_CODE", "GENERIC_NAME", "GPUID", "NLEM", "PRODUCT_CAT",
		"BASE_UNIT", "STATUS", "DATE_STATUS", "PERIOD_RPT", "DATE_SEND",
	},
	"purchaseplan": {
		"HOSP_CODE", "YEARBUDGET", "WORKING_CODE", "GENERIC_NAME", "GPUID", "NLEM",
		"QTY_USE_YEAR3", "QTY_USE_YEAR2", "QTY_USE_YEAR1", "QTY_THIS_YEAR", "PACK_SIZE",
		"BASE_UNIT", "PACK_COST", "VALUE_THIS_YEAR", "QTY_PLAN_TRIMES1", "QTY_PLAN_TRIMES2",
		"QTY_PLAN_TRIMES3", "QTY_PLAN_TRIMES4", "PERIOD_RPT", "DATE_SEND",
	},
	"receipt": {
		"HOSP_CODE", "WORKING_CODE", "TRADE_NAME", "TPUID", "VENDOR_NAME", "VENDOR_TAX_ID",
		"QTY_RCV", "PACK_SIZE", "BASE_UNIT", "PACK_COST", "TOTAL_VALUE", "LOT_NO", "EXPIRE_DATE",
		"RCV_NO", "PO_NO", "CNT_NO", "DATE_RCV", "BUY_METHOD_ID", "CO_PURCHASE_ID", "RCV_FLAG",
		"PERIOD_RPT", "DATE_SEND",
	},
	"distribution": {
		"HOSP_CODE", "WORKING_CODE", "TRADE_NAME", "TPUID", "QTY_DIS", "PACK_SIZE",
		"BASE_UNIT", "VALUE", "DIS_DEPT_GROUP", "PERIOD_RPT", "DATE_SEND",
	},
	"inventory": {
		"HOSP_CODE", "WORKING_CODE", "TRADE_NAME", "TPUID", "VENDOR_NAME", "VENDOR_TAX_ID",
		"QTY_ONHAND", "PACK_SIZE", "BASE_UNIT", "PACK_COST", "VALUE_ONHAND", "LOT_NO",
		"EXPIRE_DATE", "DATE_ONHAND", "DATE_SEND",
	},
}

// GenerateExcel creates an Excel file matching the official MOPH F5 schema
func GenerateExcel(cfg *config.AppConfig, fileType string, rows []map[string]interface{}) (string, error) {
	cols, ok := FileColumns[fileType]
	if !ok {
		return "", fmt.Errorf("unknown fileType: %s", fileType)
	}

	yearAD := cfg.Year
	if yearAD > 2400 {
		yearAD -= 543
	}
	periodRpt := fmt.Sprintf("%04d%02d", yearAD, cfg.Month)
	dateSend := time.Now().Format("20060102150405")
	dateSendDate := time.Now().Format("20060102")

	filename := fmt.Sprintf("%s_%s_%s_%s.xlsx", cfg.HospCode, fileType, periodRpt, dateSendDate)
	outputDir := cfg.OutputDir
	if outputDir == "" {
		outputDir = "./exports"
	}
	_ = os.MkdirAll(outputDir, 0755)
	fullPath := filepath.Join(outputDir, filename)

	f := excelize.NewFile()
	defer f.Close()

	var sheetName string
	switch fileType {
	case "druglist":
		sheetName = "druglist_template"
	case "purchaseplan":
		sheetName = "PURCHASEPLAN Template"
	case "receipt":
		sheetName = "receipt template"
	case "distribution":
		sheetName = "DISTRIBUTION_template"
	case "inventory":
		sheetName = "INVENTORY_template"
	default:
		sheetName = "Sheet1"
	}
	f.SetSheetName("Sheet1", sheetName)

	// Header row style
	headerStyle, _ := f.NewStyle(&excelize.Style{
		Font: &excelize.Font{Bold: true, Color: "#000000"},
		Fill: excelize.Fill{Type: "pattern", Color: []string{"#E2E8F0"}, Pattern: 1},
		Border: []excelize.Border{
			{Type: "left", Color: "#CBD5E1", Style: 1},
			{Type: "right", Color: "#CBD5E1", Style: 1},
			{Type: "top", Color: "#CBD5E1", Style: 1},
			{Type: "bottom", Color: "#CBD5E1", Style: 1},
		},
	})

	// Write Headers
	for cIdx, colName := range cols {
		cell, _ := excelize.CoordinatesToCellName(cIdx+1, 1)
		f.SetCellValue(sheetName, cell, colName)
		f.SetCellStyle(sheetName, cell, cell, headerStyle)
	}

	// Write Data Rows
	for rIdx, row := range rows {
		rowNum := rIdx + 2
		for cIdx, colName := range cols {
			cell, _ := excelize.CoordinatesToCellName(cIdx+1, rowNum)
			val := row[colName]
			if val == nil || val == "" {
				if colName == "DATE_SEND" {
					val = dateSend
				} else if colName == "PERIOD_RPT" {
					val = periodRpt
				} else if colName == "HOSP_CODE" {
					val = cfg.HospCode
				} else {
					val = ""
				}
			}
			f.SetCellValue(sheetName, cell, val)
		}
	}

	// Save to disk
	if err := f.SaveAs(fullPath); err != nil {
		return "", fmt.Errorf("save excel failed: %w", err)
	}

	return fullPath, nil
}
