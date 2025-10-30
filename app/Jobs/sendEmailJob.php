<?php

namespace App\Jobs;

use App\Mail\ReservationReceiptMail;
use App\Models\PriorityQueue;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class sendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
   
    public $tries;
    public $timeout;
    public $backoff;
    public Reservation $reservation;
    public PriorityQueue $priority;

    public function __construct(Reservation $reservation) {
        // $this->priority = $priority;
        $this->reservation = $reservation;
    }

    // public function __serialize(): array
    // {
    //     return [
    //         'reservation' => $this->reservation,
    //         'priority' => $this->priority,
    //     ];
    // }

    // public function __unserialize(array $data): void
    // {
    //     $this->reservation = $data['reservation'];
    //     $this->priority = $data['priority'];
    // }
    
    public function handle(): void
    {
        throw new \Exception("Simulated failure for testing purposes"); // Simulate failure for testing
        Mail::to($this->reservation->email)
        ->send(new ReservationReceiptMail($this->reservation));
        
    }
    
    
    public function failed(Throwable $e): void
    {
    //    $payload = $this->q->payload;

    //    $payload['fail_count'] = ($payload['fail_count'] ?? 0) +1;
    //    $payload['cut_count'] = ($payload['cut_count'] ?? 0) + 1;

    //    if($payload['fail_count'] >= 5){
    //     Log::error("this job reservarion is failed {$payload->name}");
    //     $this->q->delete();
    //     return;
    //    }
    //    Log::warning("{$payload['name']} failed. retry later");
    //    \App\Models\PriorityQueue::create([
    //     'priority' => $this->q->priority,
    //     'payload' => $payload,
    //    ]);

    logger()->error("send email job failed " . $e->getMessage());
    }
}
