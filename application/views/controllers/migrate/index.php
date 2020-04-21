<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <h1 class="text-center">Migration</h1>
            <div class="panel panel-default">
                <div class="panel-body">
                    <p class="text-center"><?php echo ($result) ? '<strong><span class="text-success fa fa-fw fa-check"></span> Version:</strong> ' . $result : 'ERROR!'; ?></p>
                    <?php echo anchor('', '<span class="fa fa-fw fa-home"></span> Back to Home', array('class' => 'btn btn-primary btn-block')); ?>
                </div>
            </div>
        </div>
    </div>
</div>