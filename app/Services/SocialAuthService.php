<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAuthService
{
    /**
     * Tìm hoặc tạo tài khoản từ dữ liệu OAuth (Google/Facebook).
     * Ưu tiên: đã liên kết provider -> trùng email (liên kết thêm) -> tạo mới.
     */
    public function resolveUser(string $provider, SocialiteUser $socialUser): User
    {
        $linked = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($linked) {
            return $linked;
        }

        $email = $socialUser->getEmail();

        if ($email && $existing = User::where('email', $email)->first()) {
            $existing->forceFill([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $existing->avatar ?: $this->downloadAvatar($socialUser->getAvatar()),
            ])->save();

            return $existing;
        }

        return User::create([
            'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Người dùng',
            // Facebook có thể không trả email -> email dự phòng để giữ ràng buộc unique/NOT NULL
            'email' => $email ?: $provider . '_' . $socialUser->getId() . '@social.local',
            'avatar' => $this->downloadAvatar($socialUser->getAvatar()),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'role' => 'user',
            'status' => 'active',
            // Tài khoản social không có mật khẩu -> đặt ngẫu nhiên để giữ ràng buộc NOT NULL
            'password' => bcrypt(Str::random(40)),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Tải avatar từ provider về storage local, trả path (như avatar upload thường).
     * Lỗi mạng/không có ảnh -> null để không chặn đăng nhập.
     */
    private function downloadAvatar(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $path = 'avatars/social_' . Str::random(24) . '.jpg';
            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
