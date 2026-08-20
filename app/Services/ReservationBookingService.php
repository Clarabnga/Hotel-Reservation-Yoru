<?php

namespace App\Services;

use App\Exceptions\NoAvailableRoomException;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReservationBookingService
{
    public function book(User $user, Room $requestedRoom, array $data): Reservation
    {
        return DB::transaction(function () use ($user, $requestedRoom, $data): Reservation {
            $rooms = Room::query()
                ->where('type', $requestedRoom->type)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $room = $this->selectRoom(
                $rooms,
                $requestedRoom->id,
                $data['check_in'],
                $data['check_out'],
            );

            $nights = CarbonImmutable::parse($data['check_in'])
                ->diffInDays(CarbonImmutable::parse($data['check_out']));

            return Reservation::create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $data['phone'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'total_price' => $nights * $room->price,
                'status' => 'pending',
            ]);
        }, 3);
    }

    /**
     * @param  Collection<int, Room>  $rooms
     */
    private function selectRoom(Collection $rooms, int $requestedRoomId, string $checkIn, string $checkOut): Room
    {
        $requestedRoom = $rooms->firstWhere('id', $requestedRoomId);

        if ($requestedRoom?->isAvailableFor($checkIn, $checkOut)) {
            return $requestedRoom;
        }

        $alternative = $rooms
            ->where('operational_status', 'active')
            ->reject(fn (Room $room): bool => $room->id === $requestedRoomId)
            ->first(fn (Room $room): bool => $room->isAvailableFor($checkIn, $checkOut));

        return $alternative ?? throw new NoAvailableRoomException;
    }
}
