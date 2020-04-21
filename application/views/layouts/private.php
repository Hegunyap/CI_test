<?php echo $yield_header; ?>

<body id="page-top" class="wi-skin fixed-sidebar fixed-nav">
    <div id="wrapper">
        <?php echo $yield_navbar_side; ?>
        <div id="page-wrapper" class="gray-bg">
            <?php echo $yield_navbar_top; ?>
            <?php echo $yield_header_page; ?>
            <?php echo $yield_alert; ?>

            <div class="wrapper wrapper-content">
                <?php echo $yield; ?>
            </div>

            <?php echo $yield_footer; ?>
        </div>
    </div>
</body>

</html>
