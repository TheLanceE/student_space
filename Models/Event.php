<?php

class Event
{    
    public ?int $eventID;
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
    public int $teacherID;

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
        int $teacherID,
        ?int $eventID = null
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
        $this->teacherID = $teacherID;
        $this->eventID = $eventID;
    }

    public function create($pdo)
    {
        $statement = $pdo->prepare("
            INSERT INTO events 
            (title, date, startTime, endTime, maxParticipants, nbrParticipants, course, type, location, description, teacherID) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        return $statement->execute([
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
            $this->teacherID
        ]);
    }

    static public function getAll($pdo)
    {
        $statement = $pdo->prepare("SELECT * FROM events ORDER BY date ASC, startTime ASC");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function getByTeacher($pdo, int $teacherID)
    {
        $statement = $pdo->prepare("SELECT * FROM events WHERE teacherID = ? ORDER BY date ASC, startTime ASC");
        $statement->execute([$teacherID]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function delete($pdo, int $id)
    {
        $statement = $pdo->prepare("DELETE FROM events WHERE eventID = ?");
        return $statement->execute([$id]);
    }
}
?>
