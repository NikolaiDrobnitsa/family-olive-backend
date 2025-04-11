<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = User::query()->where('is_admin', false);

        // Применение фильтров
        if ($this->request->has('email')) {
            $query->where('email', 'like', '%' . $this->request->email . '%');
        }

        if ($this->request->has('phone')) {
            $query->where('phone', 'like', '%' . $this->request->phone . '%');
        }

        if ($this->request->has('is_verified')) {
            $query->where('is_verified', $this->request->is_verified);
        }

        if ($this->request->has('interest_type')) {
            $query->where('interest_type', $this->request->interest_type);
        }

        if ($this->request->has('date_from')) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }

        if ($this->request->has('date_to')) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }

        if ($this->request->has('utm_source')) {
            $query->where('utm_source', 'like', '%' . $this->request->utm_source . '%');
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Имя',
            'Email',
            'Телефон',
            'Верифицирован',
            'Тип интереса',
            'IP адрес',
            'UTM Source',
            'UTM Medium',
            'UTM Campaign',
            'UTM Term',
            'UTM Content',
            'Дата регистрации',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone,
            $user->is_verified ? 'Да' : 'Нет',
            $user->interest_type,
            $user->ip_address,
            $user->utm_source,
            $user->utm_medium,
            $user->utm_campaign,
            $user->utm_term,
            $user->utm_content,
            $user->created_at->format('d.m.Y H:i:s'),
        ];
    }
}
