package tray

import (
	_ "embed"
	"fmt"
	"os"

	"github.com/getlantern/systray"
)

//go:embed icon.ico
var iconIco []byte

type TrayCallbacks struct {
	OnOpenGUI     func()
	OnSyncNow     func()
	OnExportExcel func()
	OnQuit        func()
}

func StartTray(port int, cb TrayCallbacks) {
	onReady := func() {
		systray.SetIcon(iconIco)
		systray.SetTitle("INVS Agent")
		systray.SetTooltip(fmt.Sprintf("SmartData INVS Agent (กำลังรันเบื้องหลัง :%d)", port))

		mOpen := systray.AddMenuItem("📂 เปิดหน้าต่างตั้งค่า (Open GUI)", "เปิดหน้าจอตั้งค่าและส่งข้อมูล")
		mSync := systray.AddMenuItem("🔄 ซิงค์ข้อมูลเดี๋ยวนี้ (Sync Now)", "ดึงข้อมูลจาก SQL Server และยิงเข้า SmartData API")
		mExport := systray.AddMenuItem("📊 ส่งออก Excel 5 แฟ้ม", "สร้างไฟล์ Excel ทั้ง 5 แฟ้มลงโฟลเดอร์ exports")
		systray.AddSeparator()
		mQuit := systray.AddMenuItem("❌ ออกจากโปรแกรม (Exit)", "ปิดการทำงานและปิดโปรเซสทั้งหมด")

		go func() {
			for {
				select {
				case <-mOpen.ClickedCh:
					if cb.OnOpenGUI != nil {
						cb.OnOpenGUI()
					}
				case <-mSync.ClickedCh:
					if cb.OnSyncNow != nil {
						cb.OnSyncNow()
					}
				case <-mExport.ClickedCh:
					if cb.OnExportExcel != nil {
						cb.OnExportExcel()
					}
				case <-mQuit.ClickedCh:
					if cb.OnQuit != nil {
						cb.OnQuit()
					}
					systray.Quit()
					os.Exit(0)
				}
			}
		}()
	}

	onExit := func() {
		// Called when systray exits
	}

	systray.Run(onReady, onExit)
}
