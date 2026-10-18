<?php
include 'includes/session.php';
include '../includes/slugify.php';

$selectedElectionId = isset($_GET['election_id']) ? (int) $_GET['election_id'] : 0;
$active = $conn->query("SELECT id FROM elections WHERE status = 'Active' ORDER BY start_at DESC, id DESC LIMIT 1");
if ($selectedElectionId < 1 && $active && ($activeRow = $active->fetch_assoc())) {
    $selectedElectionId = (int) $activeRow['id'];
}
$electionName = 'Election Results';
$electionMeta = '';
$electionQuery = $conn->query("SELECT e.name, et.name AS election_type, s.name AS state_name, d.name AS district_name, c.name AS constituency_name
  FROM elections e
  LEFT JOIN election_types et ON et.id = e.election_type_id
  LEFT JOIN states s ON s.id = e.state_id
  LEFT JOIN districts d ON d.id = e.district_id
  LEFT JOIN constituencies c ON c.id = e.constituency_id
  WHERE e.id = $selectedElectionId");
if ($electionQuery && ($election = $electionQuery->fetch_assoc())) {
    $electionName = $election['name'];
    $electionMeta = $election['election_type'].' - '.$election['state_name'].' / '.$election['district_name'].' / '.$election['constituency_name'];
}
$title = $electionName;
$config = @parse_ini_file('config.ini');
if (is_array($config) && !empty($config['election_title'])) {
    $title = $config['election_title'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?> - Results</title>
  <style>
    body {
      color: #222;
      font-family: Arial, sans-serif;
      margin: 32px auto;
      max-width: 900px;
    }

    h1, h2 {
      text-align: center;
    }

    h1 {
      margin-bottom: 4px;
    }

    .generated {
      color: #666;
      font-size: 13px;
      margin-bottom: 28px;
      text-align: center;
    }

    table {
      border-collapse: collapse;
      margin: 0 0 24px;
      width: 100%;
    }

    th, td {
      border: 1px solid #bbb;
      padding: 10px;
      text-align: left;
    }

    th {
      background: #e5e1d2;
    }

    .count {
      text-align: center;
      width: 160px;
    }

    .actions {
      margin-bottom: 24px;
      text-align: right;
    }

    button, a {
      background: #4b94c0;
      border: 0;
      color: #fff;
      cursor: pointer;
      display: inline-block;
      padding: 9px 14px;
      text-decoration: none;
    }

    @media print {
      .actions {
        display: none;
      }

      body {
        margin: 0;
      }
    }
  </style>
</head>
<body>
  <div class="actions">
    <a href="home.php">Back</a>
    <button type="button" onclick="window.print()">Print</button>
  </div>

  <h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
  <div class="generated">Vote tally generated on <?php echo date('F j, Y g:i A'); ?></div>
  <div class="generated"><?php echo htmlspecialchars($electionMeta, ENT_QUOTES, 'UTF-8'); ?></div>

  <?php
  $positions = $conn->query("SELECT id, description FROM positions WHERE election_id = $selectedElectionId ORDER BY priority ASC");
  if (!$positions) {
      die('Unable to load positions: '.htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
  }

  while ($position = $positions->fetch_assoc()) {
      $positionId = (int) $position['id'];
      $description = htmlspecialchars($position['description'], ENT_QUOTES, 'UTF-8');
      echo '<h2>'.$description.'</h2>';
      echo '<table><thead><tr><th>Candidate</th><th class="count">Votes</th></tr></thead><tbody>';

      $candidates = $conn->query(
          "SELECT c.firstname, c.lastname, COUNT(v.id) AS vote_count
           FROM candidates c
           LEFT JOIN votes v ON v.candidate_id = c.id
           WHERE c.position_id = $positionId AND c.election_id = $selectedElectionId
           GROUP BY c.id, c.firstname, c.lastname
           ORDER BY vote_count DESC, c.lastname ASC, c.firstname ASC"
      );
      if (!$candidates) {
          die('Unable to load results: '.htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
      }

      if ($candidates->num_rows === 0) {
          echo '<tr><td colspan="2">No candidates found.</td></tr>';
      } else {
          while ($candidate = $candidates->fetch_assoc()) {
              $name = htmlspecialchars($candidate['firstname'].' '.$candidate['lastname'], ENT_QUOTES, 'UTF-8');
              echo '<tr><td>'.$name.'</td><td class="count">'.(int) $candidate['vote_count'].'</td></tr>';
          }
      }
      echo '</tbody></table>';
  }
  ?>
</body>
</html>
