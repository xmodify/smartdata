package gui

import (
	"fmt"
	"os/exec"
	"strings"
)

// ChooseFolderDialog opens the Windows native FolderBrowserDialog
func ChooseFolderDialog(title string) (string, error) {
	if title == "" {
		title = "เลือกโฟลเดอร์สำหรับบันทึกไฟล์ Excel (INVS 5 แฟ้มยา)"
	}
	psCmd := fmt.Sprintf(`$app = New-Object -ComObject Shell.Application; $f = $app.BrowseForFolder(0, '%s', 0x00000050, 0); if ($f) { [Console]::Out.Write($f.Self.Path) }`, title)

	cmd := exec.Command("powershell", "-WindowStyle", "Hidden", "-NoProfile", "-STA", "-Command", psCmd)
	out, err := cmd.Output()
	if err != nil {
		return "", err
	}
	res := strings.TrimSpace(string(out))
	return res, nil
}
