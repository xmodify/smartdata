<?php

namespace App\Services;

use App\Models\AopodDeath;
use App\Models\AopodFetchLog;
use App\Models\SysVar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class AopodService
{
    /**
     * ดึงข้อมูลการตายจาก AOPOD แล้ว upsert ลง aopod_deaths และบันทึก log ทุกครั้ง
     * (โครงสร้างตารางอยู่ใน database/extracted_schemas.json — สร้างโดยปุ่ม Update Version)
     */
    /** อำเภอที่ต้องการเก็บข้อมูล (จับคู่ข้อความในทุกฟิลด์ของแถวที่ API ส่งมา) */
    public const DISTRICT = 'หัวตะพาน';
    public const DISTRICT_CODE = '3706';

    public static function fetch(?string $by = null): ?AopodFetchLog
    {
        foreach (['sys_var', 'aopod_deaths', 'aopod_fetch_logs'] as $t) {
            if (!Schema::hasTable($t)) {
                throw new \RuntimeException("ยังไม่มีตาราง {$t} กรุณากด Update Version (อัปเกรดโครงสร้างฐานข้อมูล) ก่อน");
            }
        }

        $log = [
            'status' => 'failed',
            'total' => 0,
            'inserted' => 0,
            'updated' => 0,
            'fetched_by' => $by,
            'fetched_at' => now(),
        ];

        try {
            $url = SysVar::find('aopod_death_api_url')?->sys_value;
            $token = SysVar::find('aopod_api_token')?->sys_value;
            if (!$url || !$token) {
                throw new \RuntimeException('ยังไม่ได้ตั้งค่า Token หรือ URL');
            }

            $response = Http::withoutVerifying()
                ->timeout(90)
                ->acceptJson()
                ->withToken($token)
                ->post($url);

            if (!$response->successful()) {
                throw new \RuntimeException('HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 300));
            }

            $rows = self::extractRows($response->json());
            $now = now();
            $received = count($rows);
            $skipped = 0;
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                // lccaattmm = รหัสพื้นที่ (จังหวัด 2 + อำเภอ 2 + ตำบล 2 + หมู่บ้าน) → 3706 = อ.หัวตะพาน
                $area = (string) ($row['lccaattmm'] ?? '');
                if (!str_starts_with($area, self::DISTRICT_CODE)) {
                    $skipped++;
                    continue;
                }

                // วันที่เสียชีวิตแยกเป็น ddate / dmon / dyear (พ.ศ.)
                $deathDate = null;
                if (!empty($row['dyear']) && !empty($row['dmon']) && !empty($row['ddate'])) {
                    $y = (int) $row['dyear'];
                    $y = $y > 2400 ? $y - 543 : $y;
                    if (checkdate((int) $row['dmon'], (int) $row['ddate'], $y)) {
                        $deathDate = sprintf('%04d-%02d-%02d', $y, $row['dmon'], $row['ddate']);
                    }
                }
                $birthday = null;
                if (!empty($row['byear']) && !empty($row['bmon']) && !empty($row['bdate'])) {
                    $by2 = (int) $row['byear'];
                    $by2 = $by2 > 2400 ? $by2 - 543 : $by2;
                    if (checkdate((int) $row['bmon'], (int) $row['bdate'], $by2)) {
                        $birthday = sprintf('%04d-%02d-%02d', $by2, $row['bmon'], $row['bdate']);
                    }
                }

                $data = [
                    'cid' => self::pick($row, ['pid']),
                    'sex' => self::pick($row, ['sex']),
                    'age' => isset($row['age']) && is_numeric($row['age']) ? (int) $row['age'] : null,
                    'birthday' => $birthday,
                    'death_date' => $deathDate,
                    'death_place' => self::pick($row, ['dplace']),
                    'icd10' => self::pick($row, ['ncause']),
                    'lccaattmm' => $area,
                    'hos_id' => self::pick($row, ['hos_id']),
                ];

                // API ไม่ส่งชื่อ-สกุล ใช้เลขบัตร + วันที่เสียชีวิตเป็นตัวระบุ
                $uid = md5(($data['cid'] ?: json_encode($row)) . '|' . ($data['death_date'] ?: ''));

                $payload = $data + ['raw_data' => json_encode($row, JSON_UNESCAPED_UNICODE), 'fetched_at' => $now];
                $existing = AopodDeath::where('uid', $uid)->first();
                if ($existing) {
                    $existing->update($payload);
                    $log['updated']++;
                } else {
                    AopodDeath::create(['uid' => $uid] + $payload);
                    $log['inserted']++;
                }
                $log['total']++;
            }

            $log['status'] = 'success';
            if ($received === 0) {
                $log['message'] = 'เชื่อมต่อสำเร็จ แต่ API ไม่ส่งข้อมูลกลับมา';
            } elseif ($log['total'] === 0) {
                $keys = is_array($rows[0] ?? null) ? implode(', ', array_slice(array_keys($rows[0]), 0, 15)) : '';
                $log['message'] = "API ส่งมา {$received} รายการ แต่ไม่พบรายการที่รหัสพื้นที่ (lccaattmm) ขึ้นต้นด้วย " . self::DISTRICT_CODE . " (ฟิลด์ที่พบ: {$keys})";
            } else {
                $log['message'] = 'ดึงข้อมูลสำเร็จ เฉพาะอำเภอ' . self::DISTRICT . " (API ส่งมา {$received} รายการ, ข้ามนอกพื้นที่ {$skipped})";
            }
        } catch (\Throwable $e) {
            $log['message'] = mb_substr($e->getMessage(), 0, 1000);
        }

        return AopodFetchLog::create($log);
    }

    /**
     * ผู้เสียชีวิตนอกสถานพยาบาล = รายที่มีใน AOPOD (อ.หัวตะพาน) แต่ไม่พบเลขบัตรในตาราง death ของ HOSxP
     * คืนค่าเป็นอ็อบเจกต์รูปแบบเดียวกับรายชื่อผู้เสียชีวิตของ HOSxP (dept = 'OUT')
     */
    public static function outOfHospital(string $startDate, string $endDate): array
    {
        if (!Schema::hasTable('aopod_deaths')) {
            return [];
        }

        $rows = AopodDeath::whereBetween('death_date', [$startDate, $endDate])
            ->orderBy('death_date')->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return [];
        }

        $hosxp = \Illuminate\Support\Facades\DB::connection('hosxp');
        $cids = $rows->pluck('cid')->filter()->unique()->values()->all();

        // เลขบัตรที่มีบันทึกการเสียชีวิตใน HOSxP แล้ว (ทุกช่วงเวลา)
        $found = [];
        foreach (array_chunk($cids, 1000) as $chunk) {
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            $res = $hosxp->select(
                // death.cid ใน HOSxP บางแถวเก็บแบบมีขีด (3-3418-00xxx-xx-x) ส่วนใหญ่ว่าง → ตัดขีด/ช่องว่างก่อนเทียบ
                // และเทียบกับ patient.cid (13 หลัก) ผ่าน hn ด้วย
                "SELECT REPLACE(REPLACE(d.cid,'-',''),' ','') AS dcid, REPLACE(REPLACE(pt.cid,'-',''),' ','') AS pcid
                 FROM death d
                 LEFT JOIN patient pt ON pt.hn = d.hn
                 WHERE REPLACE(REPLACE(d.cid,'-',''),' ','') IN ($ph) OR REPLACE(REPLACE(pt.cid,'-',''),' ','') IN ($ph)",
                array_merge($chunk, $chunk)
            );
            foreach ($res as $r) {
                if ($r->dcid) {
                    $found[$r->dcid] = true;
                }
                if ($r->pcid) {
                    $found[$r->pcid] = true;
                }
            }
        }

        $outside = $rows->filter(fn($r) => !$r->cid || !isset($found[$r->cid]))->values();
        if ($outside->isEmpty()) {
            return [];
        }

        // ชื่อ/HN จากทะเบียนผู้ป่วย HOSxP (ถ้ามี)
        $patients = [];
        foreach (array_chunk($outside->pluck('cid')->filter()->unique()->values()->all(), 1000) as $chunk) {
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            foreach ($hosxp->select("SELECT cid, hn, pname, fname, lname FROM patient WHERE cid IN ($ph)", $chunk) as $p) {
                $patients[$p->cid] = $p;
            }
        }

        // ชื่อโรค + กลุ่ม 504
        $icd = [];
        $codes = $outside->pluck('icd10')->filter()->unique()->values()->all();
        foreach (array_chunk($codes, 1000) as $chunk) {
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            foreach ($hosxp->select("SELECT code, name, tname, code504 FROM icd101 WHERE code IN ($ph)", $chunk) as $i) {
                $icd[$i->code] = $i;
            }
        }
        $groups = [];
        foreach ($hosxp->select('SELECT id, name1 FROM rpt_504_name') as $g) {
            $groups[(string) $g->id] = $g->name1;
        }

        return $outside->map(function ($r) use ($patients, $icd, $groups) {
            $p = $patients[$r->cid] ?? null;
            $i = $icd[$r->icd10] ?? null;
            $g = $i && $i->code504 !== null && $i->code504 !== '' ? ($groups[(string) $i->code504] ?? null) : null;
            return (object) [
                'cid' => $r->cid,
                'hn' => $p->hn ?? null,
                'an' => null,
                'ptname' => $p ? trim($p->pname . $p->fname . ' ' . $p->lname) : null,
                'sex' => $r->sex,
                'age' => $r->age,
                'birthday' => $r->birthday,
                'death_date' => $r->death_date ? \Carbon\Carbon::parse($r->death_date)->format('Y-m-d') : null,
                'death_time' => null,
                'icd10' => $r->icd10,
                'icdname' => $r->icd10 ? '[' . $r->icd10 . '] ' . ($i ? ($i->tname ?: $i->name) : '') : null,
                'name504' => $g,
                'dept' => 'OUT',
            ];
        })->all();
    }

    private static function extractRows($json): array
    {
        if (!is_array($json)) {
            return [];
        }
        if (array_is_list($json)) {
            return $json;
        }
        foreach (['data', 'result', 'results', 'rows', 'items', 'records'] as $k) {
            if (isset($json[$k]) && is_array($json[$k])) {
                return array_is_list($json[$k]) ? $json[$k] : self::extractRows($json[$k]);
            }
        }
        foreach ($json as $v) {
            if (is_array($v) && array_is_list($v) && $v && is_array($v[0])) {
                return $v;
            }
        }
        return [];
    }

    private static function pick(array $row, array $keys): ?string
    {
        $lower = array_change_key_case($row, CASE_LOWER);
        foreach ($keys as $k) {
            if (isset($lower[$k]) && !is_array($lower[$k]) && trim((string) $lower[$k]) !== '') {
                return trim((string) $lower[$k]);
            }
        }
        return null;
    }

    private static function date(?string $v): ?string
    {
        if (!$v) {
            return null;
        }
        try {
            $d = Carbon::parse($v);
            if ($d->year > 2400) {
                $d->subYears(543);
            }
            return $d->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
