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

    public function getEventID() { return $this->eventID;}
    public function setEventID($eventID){ $this->eventID = $eventID; }

    public function getTitle(){ return $this->title; }
    public function setTitle($title){ $this->title = $title; }

    public function getDate(){ return $this->date; }
    public function setDate($date){ $this->date = $date; }

    public function getStartTime(){ return $this->startTime; }
    public function setStartTime($startTime){ $this->startTime = $startTime; }

    public function getEndTime(){ return $this->endTime; }
    public function setEndTime($endTime){ $this->endTime = $endTime; }

    public function getMaxParticipants(){ return $this->maxParticipants; }
    public function setMaxParticipants($maxParticipants){ $this->maxParticipants = $maxParticipants; }

    public function getNbrParticipants(){ return $this->nbrParticipants; }
    public function setNbrParticipants($nbrParticipants){ $this->nbrParticipants = $nbrParticipants; }

    public function getCourse(){ return $this->course; }
    public function setCourse($course){ $this->course = $course; }

    public function getType(){ return $this->type; }
    public function setType($type){ $this->type = $type; }

    public function getLocation(){ return $this->location; }
    public function setLocation($location){ $this->location = $location; }

    public function getDescription(){ return $this->description; }
    public function setDescription($description){ $this->description = $description; }

    public function getTeacherID(){ return $this->teacherID; }
    public function setTeacherID($teacherID){ $this->teacherID = $teacherID; }





}

class Participation
{
    private int $participationID;
    private int $eventID;
    private int $userID;
    private string $comment;

    public function __construct($eventID, $userID, $comment)
    {
        $this->eventID = $eventID;
        $this->userID = $userID;
        $this->comment = $comment;
    }

    public function getID() { return $this->participationID; }
    public function setID(int $ID) { $this->particpationID = $iD; }

    public function getEventID() { return $this->eventID; }
    public function setEventID(int $eventID) {$this->eventID = $eventID; }
    
    public function getUseriD() { return $this->userID; }
    public function setUserID(int $userID) { $this->userID = $userID; }

    public function getComment() { return $this->comment; }
    public function setComment(string $comment) { $this->commenmt = $comment; }
}




?>

<?php
  /*  public function create($pdo)
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
    }*/
?>