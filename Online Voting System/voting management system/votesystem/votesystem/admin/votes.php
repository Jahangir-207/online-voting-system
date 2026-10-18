<?php include 'includes/session.php'; ?>
<?php
  $selectedElectionId = isset($_GET['election_id']) ? (int) $_GET['election_id'] : 0;
  if ($selectedElectionId < 1) {
    $active = $conn->query("SELECT id FROM elections WHERE status = 'Active' ORDER BY start_at DESC, id DESC LIMIT 1");
    $selectedElectionId = $active && ($activeRow = $active->fetch_assoc()) ? (int) $activeRow['id'] : 0;
  }
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="background-color:#F1E9D2 ;color:black ; font-size: 17px; font-family:Times ">
    <!-- Content Header (Page header) -->
    <section class="content-header" style= "color:black ; font-size: 17px; font-family:Times">
      <h1>
        VOTES
      </h1>
      <ol class="breadcrumb" style="color:black ; font-size: 17px; font-family:Times">
        <li><a href="#"><i class="fa fa-dashboard" ></i> Home</a></li>
        <li class="active" style="color:black ; font-size: 17px; font-family:Times" >Dashboard</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>
      <div class="row">
        <div class="col-xs-12">
          <div class="box"style="background-color: #d8d1bd">
            <div class="box-header with-border"style="background-color: #d8d1bd">
              <a href="#reset" data-toggle="modal" class="btn btn-danger btn-sm btn-curve"  style="background-color: #ff8e88;color:black ; font-size: 12px; font-family:Times"><i class="fa fa-refresh"></i> Reset</a>
              <form method="GET" style="display:inline; margin-left:10px">
                <select name="election_id" onchange="this.form.submit()" class="form-control input-sm" style="display:inline-block; width:auto">
                  <?php $elections = $conn->query("SELECT id,name FROM elections ORDER BY start_at DESC, id DESC"); while($election = $elections->fetch_assoc()): ?>
                    <option value="<?php echo (int) $election['id']; ?>" <?php echo $selectedElectionId === (int) $election['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($election['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endwhile; ?>
                </select>
              </form>
            </div>
            <div class="box-body">
              <table id="example1" class="table ">
                <thead>
                  <th class="hidden"></th>
                  <th>Position</th>
                  <th>Candidate</th>
                  <th>Voter</th>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT *, candidates.firstname AS canfirst, candidates.lastname AS canlast, voters.firstname AS votfirst, voters.lastname AS votlast FROM votes LEFT JOIN positions ON positions.id=votes.position_id LEFT JOIN candidates ON candidates.id=votes.candidate_id LEFT JOIN voters ON voters.id=votes.voters_id WHERE votes.election_id = $selectedElectionId ORDER BY positions.priority ASC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      echo "
                        <tr style='color:black ; font-size: 15px; font-family:Times'>
                          <td class='hidden'></td>
                          <td>".$row['description']."</td>
                          <td>".$row['canfirst'].' '.$row['canlast']."</td>
                          <td>".$row['votfirst'].' '.$row['votlast']."</td>
                        </tr>
                      ";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>   
  </div>
    
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/votes_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
