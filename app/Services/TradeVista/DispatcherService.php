<?php

namespace App\Services\TradeVista;

use App\Models\DeliveryBoy;
use App\Models\User;
use Illuminate\Support\Collection;

class DispatcherService
{
    public function documentTypes(): array
    {
        return config('tradevista.dispatcher.document_types', []);
    }

    public function dispatcherRate(?DeliveryBoy $profile): float
    {
        if ($profile && $profile->default_rate !== null) {
            return (float) $profile->default_rate;
        }

        return (float) config('tradevista.dispatcher.default_rate', 0);
    }

    public function dispatcherEtaHours(?DeliveryBoy $profile): int
    {
        if ($profile && $profile->default_eta_hours) {
            return (int) $profile->default_eta_hours;
        }

        return (int) config('tradevista.dispatcher.default_eta_hours', 48);
    }

    public function getAvailableDispatchers(?int $cityId): Collection
    {
        if (!$cityId || !config('tradevista.dispatcher.enabled')) {
            return collect();
        }

        return User::query()
            ->with('deliveryBoyProfile')
            ->where('user_type', 'delivery_boy')
            ->whereHas('deliveryBoyProfile', function ($query) use ($cityId) {
                $query->approved()
                    ->whereJsonContains('service_area_cities', (int) $cityId);
            })
            ->get()
            ->filter(function (User $user) {
                return $user->deliveryBoyProfile !== null;
            })
            ->map(function (User $user) {
                $profile = $user->deliveryBoyProfile;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'rate' => $this->dispatcherRate($profile),
                    'eta_hours' => $this->dispatcherEtaHours($profile),
                    'service_areas' => $profile->serviceAreaCityNames(),
                ];
            })
            ->values();
    }

    public function quote(int $dispatcherUserId): float
    {
        $profile = DeliveryBoy::where('user_id', $dispatcherUserId)->first();

        return $this->dispatcherRate($profile);
    }

    public function etaHours(int $dispatcherUserId): int
    {
        $profile = DeliveryBoy::where('user_id', $dispatcherUserId)->first();

        return $this->dispatcherEtaHours($profile);
    }
}
