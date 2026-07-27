<?php

namespace App\Helpers;

use App\Models\Employee;
use App\Models\Member;
use Carbon\Carbon;

/**
 * Helper class untuk mengelola logika cutover member.
 *
 * Sebelum Agustus 2026: menggunakan tabel 'members' di db iseki_aspro (sistem lama).
 * Mulai Agustus 2026: menggunakan tabel 'employees' di db iseki_rifa (sistem terintegrasi).
 */
class MemberHelper
{
    /**
     * Tanggal cutover: 1 Agustus 2026
     */
    const CUTOVER_DATE = '2026-08-01';

    /**
     * Cek apakah tanggal tertentu sudah masuk periode baru (rifa).
     * Jika $date tidak diberikan, pakai now().
     */
    public static function useRifa($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : now();
        return $checkDate->gte(Carbon::parse(self::CUTOVER_DATE));
    }

    /**
     * Ambil semua member (untuk dropdown, listing, dll).
     * Mengembalikan collection dengan properti yang konsisten:
     * Id_Member, NIK_Member, Name_Member
     *
     * @param string|null $asOfDate Tanggal acuan untuk menentukan sumber data (format Y-m-d).
     *                              Jika null, pakai now().
     */
    public static function getAllMembers($asOfDate = null)
    {
        if (self::useRifa($asOfDate)) {
            return Employee::orderBy('nama')->get()->map(function ($e) {
                return (object) [
                    'Id_Member'   => $e->id,
                    'NIK_Member'  => $e->nik,
                    'Name_Member' => $e->nama,
                ];
            });
        }

        return Member::all();
    }

    /**
     * Cari member berdasarkan NIK.
     * Prioritas: RIFA (employees) dulu, fallback ke lokal (members).
     * NIK sebagai primary key penghubung antara kedua sistem.
     */
    public static function findByNik($nik, $asOfDate = null)
    {
        // Cari di RIFA dulu — sumber data terbaru
        $e = Employee::where('nik', $nik)->first();
        if ($e) {
            return (object) [
                'Id_Member'   => $e->id,
                'NIK_Member'  => $e->nik,
                'Name_Member' => $e->nama,
            ];
        }

        // Fallback ke lokal (members) — untuk member lama yang belum di RIFA
        return Member::where('NIK_Member', $nik)->first();
    }

    /**
     * Cari member berdasarkan ID.
     * Menggunakan tanggal acuan untuk menentukan sumber data.
     *
     * @param string|null $asOfDate Tanggal acuan (format Y-m-d). Jika null, pakai now().
     */
    public static function findById($id, $asOfDate = null)
    {
        if (self::useRifa($asOfDate)) {
            $e = Employee::find($id);
            if ($e) {
                return (object) [
                    'Id_Member'   => $e->id,
                    'NIK_Member'  => $e->nik,
                    'Name_Member' => $e->nama,
                ];
            }
        }

        return Member::find($id);
    }

    /**
     * Resolve member berdasarkan ID dan tanggal report.
     * Digunakan untuk backward compatibility — data report lama tetap
     * bisa di-load meskipun sudah cutover.
     */
    public static function findByIdAndDate($id, $reportDate)
    {
        $isNewPeriod = Carbon::parse($reportDate)->gte(Carbon::parse(self::CUTOVER_DATE));

        if ($isNewPeriod) {
            $e = Employee::find($id);
            if (! $e) {
                return null;
            }

            return (object) [
                'Id_Member'   => $e->id,
                'NIK_Member'  => $e->nik,
                'Name_Member' => $e->nama,
            ];
        }

        return Member::find($id);
    }

    /**
     * Ambil member berdasarkan array of IDs — selalu cari di kedua tabel
     * (members lokal + employees RIFA) agar PIC dari era manapun tetap terbaca.
     *
     * @param string|null $asOfDate Diabaikan (dipertahankan untuk backward compatibility).
     */
    public static function getByIds(array $ids, $asOfDate = null)
    {
        if (empty($ids)) {
            return collect();
        }

        $results = collect();

        // Cari di members (lokal) dulu — untuk PIC lama
        $results = $results->concat(Member::whereIn('Id_Member', $ids)->get());

        // Cari di employees (RIFA) — di-concat belakangan, jadi menang di keyBy
        $empResults = Employee::whereIn('id', $ids)->get()->map(function ($e) {
            return (object) [
                'Id_Member'   => $e->id,
                'NIK_Member'  => $e->nik,
                'Name_Member' => $e->nama,
            ];
        });
        $results = $results->concat($empResults);

        // Deduplikasi: keyBy ambil yang terakhir → employees menang
        return $results->keyBy('Id_Member')->values();
    }

    /**
     * Ambil semua kemungkinan ID member dari kedua tabel berdasarkan NIK.
     * Digunakan agar riwayat report lama (dari tabel members) tetap muncul
     * setelah cutover login ke RIFA (employees).
     *
     * @param string $nik NIK member
     * @return array<int> Array of Id_Member / id
     */
    public static function getLinkedIds($nik)
    {
        $ids = [];

        $employee = Employee::where('nik', $nik)->first();
        if ($employee) {
            $ids[] = $employee->id;
        }

        $member = Member::where('NIK_Member', $nik)->first();
        if ($member) {
            $ids[] = $member->Id_Member;
        }

        return array_unique($ids);
    }

    /**
     * Cek apakah member dengan ID tertentu ada (untuk validasi).
     *
     * @param string|null $asOfDate Tanggal acuan (format Y-m-d). Jika null, pakai now().
     */
    public static function exists($id, $asOfDate = null): bool
    {
        if (self::useRifa($asOfDate)) {
            return Employee::where('id', $id)->exists();
        }

        return Member::where('Id_Member', $id)->exists();
    }
}
