<?php

declare(strict_types=1);

require_once 'DB.php';

class Workday
{
    const WORK_HOURS_START = '09:00:00';
    const WORK_HOURS_END = '18:00:00';
    const WORK_HOURS_DURATION = 9;

    private PDO $pdo;

    public function __construct()
    {
        date_default_timezone_set('Asia/Tashkent');
        $this->pdo = DB::connect();
    }

    public function store(array $data): array
    {
        if ($data['arrived_at'] !== '' && $data['leaved_at'] !== '') {
            try {
                $arrived_at = new DateTime($data['arrived_at']);
                $leaved_at = new DateTime($data['leaved_at']);
                $required_work_off = $this->calculateWorkOff($arrived_at, $leaved_at);
            } catch (Exception $e) {
                return ['status' => 'exception', 'message' => $e->getMessage()];
            }

            $query = "INSERT INTO daily (arrived_at, leaved_at, required_work_off, worked_off)
                        VALUES (:arrived_at, :leaved_at, :required_work_off, false)";
            
            $arrived_at = $arrived_at->format('Y-m-d H:i:s');
            $leaved_at = $leaved_at->format('Y-m-d H:i:s');

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':arrived_at', $arrived_at);
            $stmt->bindParam(':leaved_at', $leaved_at);
            $stmt->bindParam(':required_work_off', $required_work_off);
            $stmt->execute();

            return [
                'status' => 'success',
                'message' => 'Details added successfully'
            ];
        } else {
            return [
                'status' => 'failed',
                'message' => 'Please fill fields'
            ];
        }
    }

    public function getWorkDayList(): array
    {
        return $this->pdo->query("SELECT * FROM daily")->fetchAll();
    }

    public function calculateWorkOff(DateTime $arrived_at, DateTime $leaved_at): int
    {
        $workTimeInterval = $leaved_at->diff($arrived_at);
        $totalWorkTimeInMinutes = ($workTimeInterval->h * 60) + $workTimeInterval->i;

        return self::WORK_HOURS_DURATION * 60 - $totalWorkTimeInMinutes;
    }

    public function getHumanReadableDiff(int $minutes): string
    {
        if ($minutes === 0) return '0 min';
        if ($minutes < 60) return $minutes . ' min';
        if ($minutes === 60) return '1 hr';
        $hours = (int) ($minutes / 60);
        $minutes = $minutes % 60;
        return "$hours hr $minutes min";
    }

    public function getTotalWorkOffTime(): string
    {
        $workOffMinutes = (int) $this->pdo
            ->query("SELECT sum(required_work_off) as totalWorkOff FROM daily")
            ->fetch()['totalWorkOff'];

        if ($workOffMinutes < 1) return '0 min';

        return $this->getHumanReadableDiff($workOffMinutes);
    }

    public function markAsDone(int $id): array
    {
        $query = "UPDATE daily SET worked_off = true WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return [
                'status' => 'success',
                'message' => 'Successfully marked as done'
            ];
        }

        return [
            'status' => 'failed',
            'message' => 'Something went wrong'
        ];
    }
}
