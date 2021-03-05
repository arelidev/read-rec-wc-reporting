<?php
    function wc_events_reporting_daily_all_view( ) {
        $array_cat_data_csv = array();
        $currrentTimeS = current_time('timestamp');
        $dailyall_To = date("Y-m-d", $currrentTimeS);
        $dailyall_From =  date("Y-m-d", strtotime("-1 week", $currrentTimeS));

        $all_daily_data = array();

        $terms = get_terms('tribe_events_cat', array('hierarchical' => false));
        $statuses = "'wc-completed','wc-processing','wc-on-hold'";
        foreach ($terms as $k => $t):
            $product_ids = get_objects_in_term(array($t->term_id), 'tribe_events_cat');
            foreach ($product_ids as $prods) {
                $data = '';
                $data = get_order_against_product($prods, $statuses, ((isset($_GET['date_from']) && isset($_GET['date_to'])) ? array('start' => $_GET['date_from'], 'end' => $_GET['date_to']) : false));
                
                if ($data) {
                    $all_daily_data[$t->name][get_the_title($prods)] = $data;
                }
                if (is_array($data)) {
                    foreach ($data as $product => $key) {
                        $array_cat_data_csv[] = array(
                            'product' => get_the_title($prods),
                            'category' => $t->name,
                            'Participant Name' => @$key['fname'] . " " . @$key['lname'],
                            'gross' => @$key['price']
                        );
                    }
                }
            }
        endforeach;

        ExportXLS($array_cat_data_csv, 'daily-all');

        ?>
        <style>
            @media print
            {
                .woo-nav-tab-wrapper,#wpadminbar,#adminmenumain,.header_sec,.Top_banner,.footer,.notify_all_btn,.daily_reprt,.pr_link_report,.fbsection,.searchBox,.left_sec_div {
                    display: none;
                }

                #wpcontent {
                    margin: 0px;

                }
            }
            .wrap_head_filterdates_form {
                display: inline-block;
            }
            .wrap_head_fexportpdf {
                display: inline;
            }
            .wc-reports-export-wrapper {
                float: right;
            }
        </style>

        <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
            <a href="<?php echo admin_url('admin.php?page=class_reporting'); ?>" class="nav-tab">Class Reporting </a>
            <a href="<?php echo admin_url('admin.php?page=class_reporting&qtype=daily'); ?>" class="nav-tab ">Daily Summary report</a>
            <a href="<?php echo admin_url('admin.php?page=class_reporting&qtype=dailyall&date_from='.$dailyall_From.'&date_to='.$dailyall_To); ?>" class="nav-tab nav-tab-active">Daily Detailed Report</a>
        </h2>
        <div class="wrap woocommerce">
            <div class="header_rep_section daily_reprt">
                <form method="GET" class="wrap_head_filterdates_form"action="<?php echo admin_url('admin.php'); ?>">
                    <div class="wrap_head_filterdates">
                        <input type="hidden" name="page" value="class_reporting"/>
                        <input type="hidden" name="qtype" value="dailyall"/>
                        <input type="date" name="date_from" value="<?php echo $_GET['date_from']; ?>" id="date_from" class="date_p" placeholder="From date"/>
                        <input type="date" name="date_to" value="<?php echo $_GET['date_to']; ?>" id="date_to" class="date_p" placeholder="To date"/>
                        <input type="submit" value="filter" class="button"/>
                    </div>        
                </form>
                <div class="wc-reports-export-wrapper">
                    <a class="button button-primary pr_link_report" onclick="window.print();">Print</a>
                    <!--<a class="button button-primary export_csv" onclick="" href="<?php //echo make_csv_file_to_download($array_cat_data_csv); ?>">Export in CSV</a>-->
                    <a class="button button-primary export_csv" onclick="" href="<?= get_template_directory_uri().'/ReadingtonReport.xlsx' ?>">Export in XLS</a>
                    <!-- <a class="button button-primary export_csv" onclick="" href="admin.php?page=class_reporting&f=ReadingtonReport.xlsx&qtype=download">Export in XLS</a> -->
                    <form class="wrap_head_fexportpdf" method="POST" action="<?php echo admin_url('admin-ajax.php') ?>">
                        <input type="hidden" name="action" value="reprt_pdf_exp">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('reprt_pdf_exp'); ?>">
                        <input type="hidden" name="html_wrap" id="html_wrap" value="">
                        <input type="submit" value="Export as PDF" class="button"/>
                    </form>
                </div>
            </div>
        </div>
            <?php

        foreach ($all_daily_data as $terms => $pros) {      
            if (is_array($pros) && count($pros) > 0) {
                echo "<h3><i class='fa fa-chevron-right'></i> " . $terms . "</h3>";
                foreach ($pros as $products => $prods_order) {
                    if (is_array($prods_order) && count($prods_order) > 0) {
                        echo "<h5><i class='fa fa-chevron-right'></i> " . $products . "</h5>";
                        ?>
                        <table class="form-table wp-list-table widefat fixed posts table table-bordered table-report-daily detailed dataTables-listings">
                            <thead>
                                <tr>
                                    <td class="cl_name"> Participant name</td>
                                    <td class="cl_name"> Parent name</td>
                                    <td class="cl_name"> Payment mode</td>
                                    <td> Sales Amt</td> 
                                    <td> Date </td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $qtysum = 0;
                                $gross = 0;
                                $i = 0;
                                unset($prods_order['ExtraColumn']);
                                foreach ($prods_order as $products_data) {
                                    ?>                                    
                                    <tr class="<?php echo ($i % 2 == 0) ? 'odd' : 'even'; ?>">
                                        <?php
                                            $qtysum += (int) $products_data['price'];
                                            ?>
                                            <td> <?php echo $products_data['fname'] . " " . $products_data['lname']; ?></td>
                                            <td> <?php echo $products_data['Parent Name']; ?></td>
                                            <td> <?php echo $products_data['Payment mode']; ?></td>
                                            <td><?php echo wc_price($products_data['price']); ?> </td>
                                            <td> <?php echo $products_data['order-date']; ?></td>
                                            <?php
                                        }
                                        $i++;
                                        ?>
                                    </tr>
                                    <?php
                                }
                                ?>     </tbody>
                            <tfoot>
                                <tr>
                                    <td>
                                        Total
                                    </td>
                                    <td>
                                        <?php echo wc_price($qtysum); ?>
                                    </td>
                                    <td>
                                        <?php echo ""; ?>
                                    </td>
                                    <td>
                                        <?php echo ""; ?>
                                    </td>
                                    <td>
                                        <?php echo ""; ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <?php
                    }
                }
            }
        }

        
    