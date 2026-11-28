<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case KepalaSekolah = 'kepala_sekolah';
    case Guru = 'guru';
    case Staff = 'staff';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
