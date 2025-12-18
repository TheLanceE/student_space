<?php

class Event
{    
    public ?string $id;
    public string $title;
    public string $date;
    public string $startTime;
    public string $endTime;
    public int $maxParticipants;
    public int $nbrParticipants;
    public string $course;
    public string $type;
    public string $location;
    public string $description;
    public string $teacherId;

    public function __construct(
        string $title, 
        string $date, 
        string $startTime, 
        string $endTime, 
        int $maxParticipants, 
        int $nbrParticipants, 
        string $course, 
        string $type, 
        string $location, 
        string $description, 
        $teacherId,
        ?string $id = null
    ) {
        $this->title = $title;
        $this->date = $date;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->maxParticipants = $maxParticipants;
        $this->nbrParticipants = $nbrParticipants;
        $this->course = $course;
        $this->type = $type;
        $this->location = $location;
        $this->description = $description;  
        $this->teacherId = $teacherId;
        $this->id = $id ?? 'evt_' . bin2hex(random_bytes(8));
    }

    public function create($pdo)
    {
        $statement = $pdo->prepare("
            INSERT INTO events 
            (id, title, date, startTime, endTime, maxParticipants, nbrParticipants, course, type, location, description, teacherId) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        return $statement->execute([
            $this->id,
            $this->title,
            $this->date,
            $this->startTime,
            $this->endTime,
            $this->maxParticipants,
            $this->nbrParticipants,
            $this->course,
            $this->type,
            $this->location,
            $this->description,
            $this->teacherId
        ]);
    }

    static public function getAll($pdo)
    {
        $statement = $pdo->prepare("SELECT * FROM events ORDER BY date ASC, startTime ASC");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function getByTeacher($pdo, $teacherId)
    {
        $statement = $pdo->prepare("SELECT * FROM events WHERE teacherId = ? ORDER BY date ASC, startTime ASC");
        $statement->execute([$teacherId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function delete($pdo, $id)
    {
        $statement = $pdo->prepare("DELETE FROM events WHERE id = ?");
        return $statement->execute([$id]);
    }
}
?>
