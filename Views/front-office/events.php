<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Controllers/EventController.php';

$controller = new EventController($db_connection);

// Get all events (students can view all upcoming events)
$events = $controller->getAll();

// Filter to show only upcoming events (today and future)
$today = date('Y-m-d');
$upcomingEvents = array_filter($events, function($e) use ($today) {
    return ($e['date'] ?? '') >= $today;
});

// Sort by date
usort($upcomingEvents, function($a, $b) {
    return strcmp($a['date'] ?? '', $b['date'] ?? '');
});
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>EduMind+ | Events</title>
    <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../../shared-assets/css/global.css" rel="stylesheet">
    <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .event-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: var(--surface-1);
            border: 1px solid var(--border-color);
        }
        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        .event-date {
            background: var(--gradient-primary);
            color: white;
            border-radius: 10px;
            padding: 0.75rem;
            text-align: center;
            min-width: 70px;
        }
        .event-date .day {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }
        .event-date .month {
            font-size: 0.75rem;
            text-transform: uppercase;
            opacity: 0.9;
        }
        .event-type-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body data-page="front-events">
    <?php include __DIR__ . '/../partials/navbar_student.php'; ?>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0"><i class="bi bi-calendar-event me-2"></i>Upcoming Events</h1>
            <span class="badge bg-primary"><?= count($upcomingEvents) ?> events</span>
        </div>

        <?php if (empty($upcomingEvents)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Upcoming Events</h5>
                    <p class="text-muted mb-0">Check back later for new events from your teachers!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
            <?php foreach ($upcomingEvents as $event): 
                $eventDate = strtotime($event['date'] ?? 'now');
                $day = date('d', $eventDate);
                $month = date('M', $eventDate);
                $isToday = date('Y-m-d', $eventDate) === $today;
            ?>
                <div class="col-12 col-md-6">
                    <div class="card event-card shadow-sm h-100 <?= $isToday ? 'border-primary' : '' ?>">
                        <div class="card-body d-flex gap-3">
                            <div class="event-date">
                                <div class="day"><?= $day ?></div>
                                <div class="month"><?= $month ?></div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($event['title'] ?? 'Untitled Event') ?></h5>
                                    <?php if ($isToday): ?>
                                        <span class="badge bg-success">Today</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($event['description'])): ?>
                                    <p class="text-muted small mb-2"><?= htmlspecialchars($event['description']) ?></p>
                                <?php endif; ?>
                                <div class="d-flex flex-wrap gap-2 align-items-center text-muted small">
                                    <span><i class="bi bi-clock me-1"></i><?= htmlspecialchars(($event['startTime'] ?? '?') . ' - ' . ($event['endTime'] ?? '?')) ?></span>
                                    <?php if (!empty($event['course'])): ?>
                                        <span><i class="bi bi-book me-1"></i><?= htmlspecialchars($event['course']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($event['type'])): ?>
                                        <span class="badge event-type-badge bg-secondary"><?= htmlspecialchars($event['type']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>
