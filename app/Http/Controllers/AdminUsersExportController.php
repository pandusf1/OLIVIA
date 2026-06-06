<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;


class AdminUsersExportController extends Controller
{
    public function exportCsv(Request $request)
    {
        // pastikan hanya admin (mengikuti middleware auth,admin yang akan dipasang di route)

        $role = $request->string('role')->toString(); // optional
        $q = $request->string('q')->toString(); // optional

        $query = User::query();

        if ($role !== '') {
            $query->where('role', $role);
        }

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'ilike', '%' . $q . '%')
                    ->orWhere('email', 'ilike', '%' . $q . '%');
            });
        }

        $users = $query
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="users_export.csv"',
        ];

        $callback = function () use ($users) {
            $out = fopen('php://output', 'w');

            // BOM untuk Excel (supaya UTF-8 kebaca)
            fprintf($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'id',
                'name',
                'email',
                'phone',
                'role',
                'mitra_id',
                'mitra_type',
                'mitra_city',
                'created_at',
                'updated_at',
            ]);


            foreach ($users as $u) {
                $mitra = $u->mitra;

                fputcsv($out, [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->phone,
                    $u->role,
                    $u->mitra_id,
                    $mitra?->mitra_type,
                    $mitra?->city,
                    $u->created_at?->toDateTimeString(),
                    $u->updated_at?->toDateTimeString(),
                ]);
            }


            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}

