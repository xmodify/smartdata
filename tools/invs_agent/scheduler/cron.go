package scheduler

import (
	"log"
	"sync"
	"time"

	"invs_agent/config"
)

var (
	stopChan   chan struct{}
	isRunning  bool
	schedMutex sync.Mutex
	lastRunDay string
	lastRun    time.Time
)

// StartScheduler begins background time checking
func StartScheduler(syncFunc func(*config.AppConfig) error) {
	schedMutex.Lock()
	defer schedMutex.Unlock()

	if isRunning {
		return
	}

	stopChan = make(chan struct{})
	isRunning = true

	go func() {
		ticker := time.NewTicker(30 * time.Second)
		defer ticker.Stop()

		for {
			select {
			case <-stopChan:
				return
			case now := <-ticker.C:
				cfg := config.GetConfig()
				if !cfg.Schedule.Enabled {
					continue
				}

				shouldRun := false
				todayStr := now.Format("2006-01-02")
				currentHHMM := now.Format("15:04")

				// Daily Schedule (e.g. at 01:00)
				if cfg.Schedule.DailyTime != "" && cfg.Schedule.DailyTime == currentHHMM {
					if lastRunDay != todayStr {
						shouldRun = true
						lastRunDay = todayStr
					}
				}

				// Interval Schedule (e.g. every N hours)
				if cfg.Schedule.IntervalHours > 0 {
					if lastRun.IsZero() || time.Since(lastRun) >= time.Duration(cfg.Schedule.IntervalHours)*time.Hour {
						shouldRun = true
					}
				}

				if shouldRun {
					lastRun = now
					log.Printf("[Scheduler] Triggering scheduled sync at %s...", now.Format("2006-01-02 15:04:05"))
					go func() {
						schedCfg := config.GetScheduledSyncConfig(config.GetConfig())
						log.Printf("[Scheduler] Running automated sync - Period: %04d%02d, Inventory Date: %s (Yesterday)",
							schedCfg.Year, schedCfg.Month, schedCfg.Selection.InventoryDate)
						if err := syncFunc(schedCfg); err != nil {
							log.Printf("[Scheduler] Sync execution error: %v", err)
						} else {
							log.Printf("[Scheduler] Sync completed successfully.")
						}
					}()
				}
			}
		}
	}()
}

// StopScheduler stops the background scheduler loop
func StopScheduler() {
	schedMutex.Lock()
	defer schedMutex.Unlock()
	if isRunning && stopChan != nil {
		close(stopChan)
		isRunning = false
	}
}
