<?php

class Event
{    
    public int $eventID;
    public string $title;
    public DateTime $date;
    public DateTime $startTime;
    public DateTime $endTime;
    public int $maxParticipants;
    public int $nbrParticipants;
    public string $course;
    public string $type;
    public string $location;
    public string $description;
    public int $teacherID;

    public function __construct(string $title, DateTime $date, DateTime $startTime, DateTime $endTime, int $maxParticipants, int $nbrParticipants, string $course, string $type, string $location, string $description, int $teacherID)
    {
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
    }

    public function create($pdo)
    {
        $teacherID = 0;

        $statement = $pdo->prepare("
            INSERT INTO events 
            (title, date, startTime, endTime, maxParticipants, nbrParticipants, course, type, location, description, teacherID) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $statement->execute([
            $this->title,
            $this->date->format('Y-m-d'),
            $this->startTime->format('H:i:s'),
            $this->endTime->format('H:i:s'),
            $this->maxParticipants,
            $this->nbrParticipants,
            $this->course,
            $this->type,
            $this->location,
            $this->description,
            $teacherID
        ]);
    }

    static public function delete($pdo, int $id)
    {
        $statement = $pdo->prepare("DELETE FROM events WHERE eventID = ?");
        $statement->execute([$id]);
    }

    static public function incrementParticipant($pdo,$id)
    {
        $statement = $pdo->prepare("UPDATE events SET nbrParticipants = nbrParticipants + ? WHERE eventID = ?");
        $statement->execute([1, $id]);
    }

    static public function getAll($pdo)
    {
        $statement = $pdo->prepare("SELECT * FROM events");
        $statement->execute();
        $events = $statement->fetchAll(PDO::FETCH_ASSOC);
    
        return $events;
    }

    static public function getAllTeacher($pdo, $teacherID)
    {
        $statement = $pdo->prepare("SELECT * FROM events WHERE teacherID = ?");
        $statement->execute([$teacherID]);
        $events = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $events;
    }
}
?>