<?php
    function wc_events_reporting_base_view( ) {
        global $post;

        $currrentTimeS = current_time('timestamp');
        $dailyall_To = date("Y-m-d", $currrentTimeS);
        $dailyall_From =  date("Y-m-d", strtotime("-1 week", $currrentTimeS));
        
        $args = array(  
            'post_type' => 'tribe_events',
            'post_status' => 'publish',
            'posts_per_page' => -1, 
            'orderby' => 'title', 
            'order' => 'ASC', 
        );
    
        $loop = new WP_Query( $args ); 
    
        $seasonsFilter = get_terms('tribe_events_seasons');
        $categoryFilter = get_terms('tribe_events_cat');
        ?>

        <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">

        <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
            <a href="<?php echo admin_url('admin.php?page=class_reporting'); ?>" class="nav-tab nav-tab-active">Class Reporting </a>
            <a href="<?php echo admin_url('admin.php?page=class_reporting&qtype=daily'); ?>" class="nav-tab">Daily Summary report</a>
            <a href="<?php echo admin_url('admin.php?page=class_reporting&qtype=dailyall&date_from='.$dailyall_From.'&date_to='.$dailyall_To); ?>" class="nav-tab" >Daily Detailed Report</a>
        </h2>
        <div style="float: right; margin: 12px 0px;">
            <select name="categories-filter">
                <option value="">Show All Categories</option>    
                <?php foreach( $categoryFilter as $filter ): ?>
                    <option value="<?= $filter->name ?>"><?= $filter->name ?></option>
                <?php endforeach; ?>
            </select>

            <select name="seasons-filter">
                <option value="">Show All Seasons</option>
                <?php foreach( $seasonsFilter as $filter ): ?>
                    <option value="<?= $filter->name ?>"><?= $filter->name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <table class="form-table wp-list-table widefat fixed posts all-products-report" id="dataTables-listings13">
        <thead>
            <tr>
                <td>From</td>
                <td>To</td>
                <td>Event</td>
                <td>Seats sold</td>
                <td>View enrollments</td>
                <td>View detailed</td>
                <td>View waitlists</td>
                <td style="display: none" class="seasons">Season</td>
                <td style="display: none" class="categories">Categories</td>
            </tr>
        </thead>
        <tbody>

        <?php
                    
            while ( $loop->have_posts() ) : $loop->the_post();
                $season = wp_get_post_terms( $post->ID, "tribe_events_seasons");
                // $product_cat = wp_get_post_terms($post, "product_cat");
                $product_cat = wp_get_post_terms($post->ID, "tribe_events_cat");


                $list = new Tribe__Tickets__Event_Repository();
                $results = new stdClass();
                $results->end = DateTime::createFromFormat ("Y-m-d H:i:s", get_post_meta( get_the_ID(), '_EventEndDate', true ));
                $results->start = DateTime::createFromFormat ("Y-m-d H:i:s", get_post_meta( get_the_ID(), '_EventStartDate', true));
                $seats = Tribe__Tickets__Tickets::get_event_attendees_count( $post->ID );
        ?>
        <tr>
            <td>    
                <?= $results->start->format('Y-M-d') ?>
            </td>
            <td>
                <?= $results->end->format('Y-M-d') ?>
            </td>
            <td>
                <a href="<?php echo get_permalink($post); ?>">
                    <?= $post->post_title ?>
                </a>
            </td>
            <td>
                <?= $seats; ?>
            <td>
                <!-- Enrollment link -->
                <a target="_blank" href="<?php echo add_query_arg(array('page' => 'class_reporting', 'event_id' => $post->ID, 'qtype' => 'all'), admin_url('admin.php')); ?>">
                    View enrollment
                </a>
            </td>
            <td>
                <a target="_blank" href="<?php echo add_query_arg(array('page' => 'class_reporting', 'event_id' => $post->ID, 'qtype' => 'all_detailed'), admin_url('admin.php')); ?>">
                    View detailed
                </a>
            </td>
            <td>
                <a target="_blank" href="<?php echo add_query_arg(array('page' => 'class_reporting', 'event_id' => $post->ID, 'qtype' => 'waitlist'), admin_url('admin.php')); ?>">
                    View waitlist
                </a>
            </td>
            <td style="display: none" class="season">
                <?=( @$season[0]->name );?>
            </td>
            <td style="display: none" class="categories">
            <?php
                foreach( $product_cat as $cat ):
                    echo($cat->name).'; ';
                endforeach;
            ?>
            
            </td>
        </tr>

        <?php   
            endwhile;
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td>From</td>
                <td>To</td>
                <td>Event</td>
                <td>Seats sold</td>
                <td class="no-search">View enrollments</td>
                <td class="no-search">View detailed</td>
                <td class="no-search">View waitlists</td>
                <td style="display: none">Season</td>
                <td style="display: none">Categories</td>
            </tr>
        </tfoot>
    </table>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script>
        jQuery(window).load(function() {
            if (jQuery('#dataTables-listings13').length > 0) {
                jQuery('#dataTables-listings13 tfoot td').each(function(index) {
                    var title = jQuery(this).text();
                    if (!jQuery(this).hasClass('no-search')) {
                        jQuery(this).html('<input type="text" style="max-width:100% !important;    margin-left: 0px;    padding-left: 5px;"placeholder="' + title + '" />');
                    }
                });

                var table = jQuery('#dataTables-listings13').DataTable( {
                    "order": [[ 0, "desc" ]],
                    'length': 10,
                    "paging": true
                });

                // Apply the search
                table.columns().every(function() {
                    var that = this;
                    jQuery('input', this.footer()).on('keyup change', function() {
                        if (that.search() !== this.value) {
                            that.search(this.value).draw();
                        }
                    });
                });

                jQuery('select[name=categories-filter]').on('change', function() {
                    var val = this.value
                    console.log( val )
                    table
                        .columns( '.categories' )
                        .search( val )
                        .draw();
                });

                jQuery('select[name=seasons-filter]').on('change', function() {
                    var val = this.value
                    console.log( val )
                    table
                        .columns( '.seasons' )
                        .search( val )
                        .draw();
                });
            }
        })
    </script>

    <?php
    }