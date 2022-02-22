<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<input type='checkbox' onClick='checkAllBoxes(this)' id='checkAll' name='checkAll' />
<label for='checkAll'>Check All</label>

<input type='checkbox' onClick='checkRPIBoxes(this)' id='checkRPI' name='checkRPI' />
<label for='checkRPI'>Check New</label>

<input type='checkbox' onClick='checkFTPBoxes(this)' id='checkFTP' name='checkFTP' />
<label for='checkFTP'>Check Old</label>

<form action='' method='post'>
    <?php foreach ($this->stations as $station) : ?>
        <input 
            type='checkbox' 
            onClick='changeCheckBox()' 
            class='custom_checkbox <?php echo $station->transfer_type ?> <?php echo $station->status ?>'
            name='station[]'
            value='<?php echo $station->id ?>'
            id='station<?php echo $station->id ?>'
            <?php echo $station->checked ?>
        />
        <label for='station<?php echo $station->id ?>'><?php echo $station->name ?></label>
    <?php endforeach; ?>

    <label for='startDate'>From </label>
    <input type='date' name='startDate' min='2011-01-01' max='<?php echo $this->today ?>' value='<?php echo $this->start_date ?>' required/>

    <label for='endDate'>To </label>
    <input type='date' name='endDate' min='2011-01-01' max='<?php echo $this->today ?>' value='<?php echo $this->end_date ?>' required/>

    <input name='submit' type='submit' />
</form>
<script>
    let dataset = [
        <?php foreach ($this->selected_stations as $station) : ?>
            {
                "measure": "<?php echo $station ?>",
                "interval_s": 300,
                "data": [
                    <?php foreach ($this->availability as $file) : ?>
                        ["<?php echo $file->start ?>", 1, "<?php echo date('Y-M-d h:i:s', strtotime($file->start)->add(new DateInterval('PT5M'))) ?>"],
                    <?php endforeach; ?>
                ]
            },
        <?php endforeach; ?>
    ];

    let options = {};

    let chart = visavail.generate(options, dataset);
    console.log(dataset);
</script>
<?php print_r($this->selected_stations) ?>
