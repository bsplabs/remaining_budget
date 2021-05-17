<table class="table table-bordered border-primary">
      <thead>
        <?php //for ($i = 0; $i < 2; $i++) { ?>
          <!-- <tr> -->
            <?php 
              // for ($j = 0; $j < 10; $j++) {
              //   echo "<th scope='col'>" . $spreadSheetAry[$i][$j] . "</th>";
              // }
            ?>
          <!-- </tr> -->
        <?php //} ?>
        <tr>
          <th>Series</th>
          <th>Doc. No.</th>
          <th centered>Receive</th>
          <th>Invoice</th>
          <th>Ads Credit Note</th>
        </tr>
      </thead>

      <tbody>
        <?php for ($i = 2; $i < $sheetCount; $i++) { ?>
          <tr>
            <?php 
              // for ($j = 0; $j < 10; $j++) {
              //   echo "<td>" . $spreadSheetAry[$i][$j] . "</td>";
              // }
            ?>
            <?php echo "<td>" . $spreadSheetAry[$i][2] . "</td>"; ?>
            <?php echo "<td>" . $spreadSheetAry[$i][3] . "</td>"; ?>
            <?php
              // $doc_no_prefix = explode(" ", $spreadSheetAry[$i][2])[0];
              $doc_no_prefix = ($spreadSheetAry[$i][2])[0];
              $doc_no_prefix .= ($spreadSheetAry[$i][2])[1];
              if ($doc_no_prefix === "IN") {
                echo "<td></td>";
                echo "<td>T</td>";
                echo "<td></td>";
              } else if ($doc_no_prefix === "IV") {
                echo "<td>T</td>";
                echo "<td></td>";
                echo "<td></td>";
              } else if ($doc_no_prefix === "CV" || $doc_no_prefix === "CN") {
                echo "<td></td>";
                echo "<td></td>";
                echo "<td>T</td>";
              }
            ?>
          </tr>
        <?php } ?>
      </tbody>
    </table>