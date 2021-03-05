<?php
    function wc_events_reporting_daily_view( ) {
        $currrentTimeS = current_time('timestamp');
        $dailyall_To = date("Y-m-d", $currrentTimeS);
        $dailyall_From =  date("Y-m-d", strtotime("-1 week", $currrentTimeS));

        $terms = get_terms('tribe_events_cat', array('hierarchical' => false));
        $terms_Id = wp_list_pluck($terms, 'term_id');
        ?>
        <style>
            .ui-datepicker{display:none}        
        </style>

        <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
            <a href="<?php echo admin_url('admin.php?page=class_reporting'); ?>" class="nav-tab">Class Reporting </a>
            <a href="<?php echo admin_url('admin.php?page=class_reporting&qtype=daily'); ?>" class="nav-tab nav-tab-active">Daily Summary report</a>
            <a href="<?php echo admin_url('admin.php?page=class_reporting&qtype=dailyall&date_from='.$dailyall_From.'&date_to='.$dailyall_To); ?>" class="nav-tab" >Daily Detailed Report</a>
        </h2>
        <div class="wrap woocommerce">
            <div class="header_rep_section daily_reprt">
                <form method="GET" class="wrap_head_filterdates_form"action="<?php echo admin_url('admin.php'); ?>">
                    <div class="wrap_head_filterdates">
                        <input type="hidden" name="page" value="class_reporting"/>
                        <input type="hidden" name="qtype" value="daily"/>
                        <input type="date" name="date_from" value="<?php echo @$_GET['date_from']; ?>" id="date_from" class="date_p" placeholder="From date"/>
                        <input type="date" name="date_to" value="<?php echo @$_GET['date_to']; ?>" id="date_to" class="date_p" placeholder="To date"/>
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
            <hr>
            <div class="export-wrap">
                <?php
                if (isset($_GET['date_from']) && isset($_GET['date_to'])) {
                    ?>
                    <span style='font-size: 14px; font-weight: bold;'>  You are viewing records from <?php echo $_GET['date_from']; ?> to <?php echo $_GET['date_to']; ?>. </span>
                    <?php
                }
                ?>
                <?php
                $tableHtml = "";
                foreach ($terms_Id as $k => $t):
                    ob_start();
                    $termsName = (get_term_by("term_taxonomy_id", $t, "product_cat"));
                    $ReportArr = hm_sbp_export_body2(null, true, $t, ((isset($_GET['date_from']) && isset($_GET['date_to'])) ? array('start' => $_GET['date_from'], 'end' => $_GET['date_to']) : false));

                    if (is_array($ReportArr) && count($ReportArr) > 0) {
                        echo "<h3><i class='fa fa-chevron-right'></i> " . $termsName->name . "</h3>";
                        ?>
                        <table class="form-table wp-list-table widefat fixed posts table table-bordered table-report-daily dataTables-listings b">
                    
                            <thead>
                                <tr>
                                    <td class="cl_name"> Class Name</td>
                                    <td> Sold </td>
                                    <td> Check/COD Pmt. </td>
                                    <td> Sold </td>
                                    <td> CC payment </td>
                                    <td> Total Sold </td>
                                    <td> Gross sales</td> 
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $qtysum = 0;
                                $gross = 0;
                                $qtycc = 0;
                                $grosscc = 0;
                                $qtychk = 0;
                                $grosschk = 0;
                                $totalSale = 0;
                                $totalQunatity = 0;
                                foreach ($ReportArr as $key1 => $products_data) {
                                    $soldPricenps = 0;
                                    $soldPricecheque = 0;
                                    if (count($products_data["nps_gateway"]) > 0) {
                    
                                        foreach ($products_data["nps_gateway"] as $key2 => $val2) {
                                            $soldPricenps+=(float) $val2["soldPrice"];
                                        }
                                    }
                                    if (@count($products_data["cheque"]) > 0) {
                                        $soldPricecheque = 0;
                                        foreach ($products_data["cheque"] as $key2 => $val2) {
                                            $soldPricecheque+=(float) $val2["soldPrice"];
                                        }
                                    }
                                    $countNPS = count($products_data["nps_gateway"]) ? count($products_data["nps_gateway"]) : 0;
                                    @$countCHEQUE = count($products_data["cheque"]) ? count($products_data["cheque"]) : 0;
                    
                                    $qtycc += (int) $countNPS;
                                    $grosscc += (float) $soldPricenps;
                    
                                    $qtychk += (int) @count($products_data["cheque"]);
                                    $grosschk += (float) $soldPricecheque;
                                    $totalSale = $soldPricenps + $soldPricecheque;
                                    @$totalQunatity = count($products_data["nps_gateway"]) + count($products_data["cheque"]);
                                    $qtysum += (int) $totalQunatity;
                                    $gross += (float) $totalSale;
                    
                    
                                    $csvExpArray[] = array(
                                        'Category' => $termsName->name,
                                        'name' => get_the_title($key1),
                                        'PaidByCheck' => $countCHEQUE,
                                        'amountBYcheck' => ($soldPricecheque),
                                        'PaidByCC' => $countNPS,
                                        'amountBYcc' => ($soldPricenps),
                                        'Qty' => $totalQunatity,
                                        'totalSale' => ($totalSale)
                                    );
                                    ?>
                                    <tr>
                                        <td> <?php echo get_the_title($key1); ?></td>
                                        <td> <?php echo $countCHEQUE ?></td>
                                        <td> <?php echo $soldPricecheque ? wc_price($soldPricecheque) : 0; ?></td>
                                        <td> <?php echo $countNPS ?></td>
                                        <td> <?php echo $soldPricenps ? wc_price($soldPricenps) : 0; ?></td>
                                        <td><?php echo $totalQunatity ?> </td>
                                        <td> <?php echo wc_price($totalSale); ?></td>
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
                                        <?php echo $qtychk; ?>     
                                    </td>
                                    <td>
                                        <?php echo wc_price($grosschk); ?>    
                                    </td>
                                    <td>
                                        <?php echo $qtycc; ?>    
                                    </td>
                                    <td>
                                        <?php echo wc_price($grosscc); ?>    
                                    </td>
                                    <td>
                                        <?php echo $qtysum; ?>
                                    </td>
                                    <td>
                                        <?php echo wc_price($gross); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <?php
                    }



                $tableHtml .= ob_get_clean();
                endforeach;
                echo $tableHtml;
                make_csv_file_to_download($csvExpArray);
                ExportXLS($csvExpArray, 'daily');
                ?>
            </div>
        </div>
        <script>
            var tableHtml = <?php echo $tableHtml ? json_encode(array("tableHtml" => $tableHtml)) : "''"; ?>;
            document.getElementById("html_wrap").value = tableHtml.tableHtml;
        </script>
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
        <?php
        return;
    
    }