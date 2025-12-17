# EduMind – Events Module

> **very good website, trust**

---

## Database Overview

The database is called **EduMind** and contains the following tables:

---

### Events Table

```sql
eventID (INT, AUTO_INCREMENT, PRIMARY KEY)
title (VARCHAR)
date (DATE)
startTime (TIME)
endTime (TIME)
maxParticipants (INT)
nbrParticipants (INT)
course (VARCHAR)
type (VARCHAR)
location (VARCHAR)
description (TEXT)
teacherID (INT)        -- foreign key to the user table
```

---

### Participation Table

```sql
participationID (INT, AUTO_INCREMENT, PRIMARY KEY)
id_event (INT)         -- foreign key to events.eventID
id_user (INT)          -- foreign key to students/users
attachement (VARCHAR)  -- unused, kept just in case
comment (TEXT)         -- optional student comment when joining
```

---

## ‍ Teacher Side — `eventsTeacher.php`

**Features:**

* Form to create new events:

  * title, date, times, course, type, location,
  * max participants, description, links, recurring flag
* Location field only applies to **Lecture** type

  * cleared automatically otherwise in the controller
* List of teacher’s own events

  * `teacherID = 1` (hard-coded, plug user code here)
* Inline edit form per event

  * edit form appears after pressing the edit button
* Delete button per event

---

## Student Side — `eventsFront.php`

**Features:**

* Card-based layout for all events
* Pagination + client-side search

  * fake pagination using JS + HTML
  * PHP pagination would require rewriting everything
* Students can:

  * join events (optional comment)
  * leave events
* Join button disabled when event is full
* `userID = 899` hard-coded (plug user code here)

---

## ️ Admin Side — `eventsAdmin.php`

**Features:**

* Table view of all events
* Delete button for events
* Chart.js visualizations (made with chat):

  * Doughnut chart — events distribution by type
  * Bar chart — number of participants per event

---

## ️ Controller — `eventsController.php`

Handles all core logic:

* Create new event
* Edit existing event
* Delete event

  * also clears participations from the participation table
* Student join event
* Student leave event

---

*That’s it. Simple. Clean. Works.*
