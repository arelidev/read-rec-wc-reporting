<?php
function wc_events_reporting_all_detailed_view() {
    
$attendees = tribe( 'tickets.attendees' );

$attendees->attendees_table = new Tribe__Tickets__Attendees_Table();
$attendees->attendees_table->prepare_items();

$event_id = $_GET['event_id'];
$event    = get_post($event_id);
$tickets  = Tribe__Tickets__Tickets::get_event_tickets( $event_id );
$pto      = get_post_type_object( $event->post_type );
$singular = $pto->labels->singular_name;

// $tableAttendees = Tribe__Tickets__Tickets::get_event_attendees( (int) $event_id );
$tableAttendees = $attendees->attendees_table->items;

if (@$tableAttendees[0]) {
	$keys = array_keys($tableAttendees[0]['attendee_meta']);
}

$show_title = apply_filters( 'tribe_tickets_attendees_show_title', true, $attendees );
?>
<link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
<style>
	@media print
	{
		.woo-nav-tab-wrapper,#wpadminbar,#adminmenumain,.header_sec,.Top_banner,.footer,.notify_all_btn,.daily_reprt,.pr_link_report,.fbsection,.searchBox,.left_sec_div {
			display: none;
		}
		#wpcontent {
			margin: 0px;
		}

		.error, .settings-error, .update-nag, .wc-reports-export-wrapper, .filters, .search-box, #wpfooter   {
			display: none;
		}

		table {
			width: 100vw;
			max-width: 200vw;
			zoom: 0.5;
		}

		table th {
			padding: 0px !important;
			width: auto !important;
			min-width: none !important;
		}

		table td {
			vertical-align:top !important;
			font-weight: 600;
			color: black !important;
		}

		.dataTables_length, .dataTables_filter {
			display: none;
		}
	}

	@media screen {
		table th {
			width: 125px !important;
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
		margin-bottom: 12px;
	}

</style>
<div class="wrap tribe-report-page">
	<?php if ( $show_title ) : ?>
		<h1>
			<?php
			echo esc_html(
				sprintf(
				// Translators: 1: the post title, 2: the post ID.
					_x( 'Attendees for: %1$s [#%2$d]', 'attendees report screen heading', 'event-tickets' ),
					get_the_title( $event_id ),
					$event_id
				)
			);
			?>
		</h1>
	<?php endif; ?>
	
	<div id="tribe-attendees-summary" class="welcome-panel tribe-report-panel">
		<div class="welcome-panel-content">
			<div class="welcome-panel-column-container">

				<?php
				/**
				 * Fires before the individual panels within the attendee screen summary
				 * are rendered.
				 *
				 * @param int $event_id
				 */
				do_action( 'tribe_events_tickets_attendees_event_details_top', $event_id );
				?>

				<div class="welcome-panel-column welcome-panel-first">
					<h3><?php
						echo esc_html(
							sprintf(
								_x( '%s Details', 'attendee screen summary', 'event-tickets' ),
								$singular
							)
						); ?>
					</h3>

					<ul>
						<?php
						/**
						 * Provides an action that allows for the injections of fields at the top of the event details meta ul
						 *
						 * @var $event_id
						 */
						do_action( 'tribe_tickets_attendees_event_details_list_top', $event_id );

						/**
						 * Provides an action that allows for the injections of fields at the bottom of the event details meta ul
						 *
						 * @var $event_id
						 */
						do_action( 'tribe_tickets_attendees_event_details_list_bottom', $event_id );
						?>
					</ul>
					<?php
					/**
					 * Provides an opportunity for various action links to be added below
					 * the event name, within the attendee screen.
					 *
					 * @param int $event_id
					 */
					do_action( 'tribe_tickets_attendees_do_event_action_links', $event_id );

					/**
					 * Provides an opportunity for various action links to be added below
					 * the action links
					 *
					 * @param int $event_id
					 */
					do_action( 'tribe_events_tickets_attendees_event_details_bottom', $event_id ); ?>

				</div>
				<div class="welcome-panel-column welcome-panel-middle">
					<h3><?php echo esc_html_x( 'Overview', 'attendee screen summary', 'event-tickets' ); ?></h3>
					<?php do_action( 'tribe_events_tickets_attendees_ticket_sales_top', $event_id ); ?>
					<ul>
						<?php
						/** @var Tribe__Tickets__Ticket_Object $ticket */
						foreach ( $tickets as $ticket ) {
							$ticket_name = sprintf( '%s [#%d]', $ticket->name, $ticket->ID );
							?>
							<li>
								<strong><?php echo esc_html( $ticket_name ) ?>:&nbsp;</strong><?php
								echo esc_html( tribe_tickets_get_ticket_stock_message( $ticket ) );
								?></li>
						<?php } ?>
					</ul>
					<?php do_action( 'tribe_events_tickets_attendees_ticket_sales_bottom', $event_id ); ?>
				</div>
				<div class="welcome-panel-column welcome-panel-last alternate">
					<?php
					/**
					 * Fires before the main body of attendee totals are rendered.
					 *
					 * @param int $event_id
					 */
					do_action( 'tribe_events_tickets_attendees_totals_top', $event_id );

					/**
					 * Trigger for the creation of attendee totals within the attendee
					 * screen summary box.
					 *
					 * @param int $event_id
					 */
					do_action( 'tribe_tickets_attendees_totals', $event_id );

					/**
					 * Fires after the main body of attendee totals are rendered.
					 *
					 * @param int $event_id
					 */
					do_action( 'tribe_events_tickets_attendees_totals_bottom', $event_id );
					?>
				</div>
			</div>
		</div>
	</div>
	<?php do_action( 'tribe_events_tickets_attendees_event_summary_table_after', $event_id ); ?>
	<div class="wc-reports-export-wrapper">
		<a class="button button-primary pr_link_report" onclick="window.print();">Print</a>
		<!--<a class="button button-primary export_csv" onclick="" href="<?php //echo make_csv_file_to_download($array_cat_data_csv); ?>">Export in CSV</a>-->
		<a class="button button-primary export_csv" onclick="" href="<?= get_template_directory_uri().'/ReadingtonReport.xlsx' ?>">Export in XLS</a>
		<!-- <a class="button button-primary export_csv" onclick="" href="admin.php?page=class_reporting&f=ReadingtonReport.xlsx&qtype=download">Export in XLS</a> -->
		<form class="wrap_head_fexportpdf" method="POST" action="<?php echo admin_url('admin-ajax.php') ?>">
			<input type="hidden" name="action" value="reprt_pdf_exp">
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('reprt_pdf_exp'); ?>">
			<input type="hidden" name="html_wrap" id="html_wrap" value="">
			<!-- <input type="submit" value="Export as PDF" class="button"/> -->
		</form>
	</div>
	<br>
	<form id="topics-filter" class="topics-filter" method="post">
		<input type="hidden" name="<?php echo esc_attr( is_admin() ? 'page' : 'tribe[page]' ); ?>" value="<?php echo esc_attr( isset( $_GET['page'] ) ? $_GET['page'] : '' ); ?>" />
		<input type="hidden" name="<?php echo esc_attr( is_admin() ? 'event_id' : 'tribe[event_id]' ); ?>" id="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
		<input type="hidden" name="<?php echo esc_attr( is_admin() ? 'post_type' : 'tribe[post_type]' ); ?>" value="<?php echo esc_attr( $event->post_type ); ?>" />
		<?php // $attendees->attendees_table->search_box( __( 'Search attendees', 'event-tickets' ), 'attendees-search' ); ?>
        <table id="form-table" class="form-table wp-list-table widefat fixed posts table table-bordered all-products-report_customs table-part-all dataTables-listings dataTable">
            <thead>
                <th width="70">
                    Order Date
                </th>
				<?php if ( in_array('participant-name', $keys ) || in_array('name-of-participant', $keys )  ) : ?>
				<th width="70">
					Last Name
				</th>
                <th width="70">
					First Name
				</th>
				<?php endif ?>
                <th width="70">
                    Parent Email
                </th>
				<th width="70">
                    Primary Phone
                </th>
				<?php foreach($keys as $key) {
					if ($key != 'participant-name' && $key != 'name-of-participant'):
						echo ('<th width="70">'.ucwords( str_replace('-',' ', $key) ).'</th>');
					endif;
				} ?>
            </thead>
            <tbody>
				<?php foreach( $tableAttendees as $index => $row ): 
					$order = new WC_Order($row["order_id"]);
					@$order_date = $order->order_date;		
					$order = $order->get_data();
					@$phone = $order['billing']['phone'];
					$tableAttendees[$index]['order_date'] = $order_date;
					$tableAttendees[$index]['phone'] = $phone;
				?>
                <tr>
                    <td>
                        <?= $order_date ?>
                    </td>
					<?php
						if ( in_array('participant-name', $keys ) ) {
							$pieces = explode(' ',$row['attendee_meta']['participant-name']['value'], 2);
							echo ('<td>'.@$pieces[1].'</td>');
							echo ('<td>'.@$pieces[0].'</td>');
						}
				   ?>
				   	<?php
						if ( in_array('name-of-participant', $keys ) ) {
							$pieces = explode(' ',$row['attendee_meta']['name-of-participant']['value'], 2);
							echo ('<td>'.@$pieces[1].'</td>');
							echo ('<td>'.@$pieces[0].'</td>');
						}
				   ?>
                    <td>
                        <?= @$row['purchaser_email'] ?>
                    </td>
					<td>
                        <?= @$phone ?>
                    </td>
					<?php foreach($keys as $key) {
						if (is_array($row['attendee_meta'][$key]['value'])):
							$variableKey = array_keys($row['attendee_meta'][$key]['value']);
							echo ('<td>'. $row['attendee_meta'][$key]['value'][$variableKey[0]]  .'</td>');
						elseif ($key != 'participant-name' && $key != 'name-of-participant'):
							echo ('<td>'.$row['attendee_meta'][$key]['value'].'</td>');
						endif;
					} ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
	</form>
	<?php 
		//make_csv_file_to_download($tableAttendees);
		ExportXLS($tableAttendees, 'class_detailed'); 
	?>
</div>

<link rel="stylesheet" href="<?= plugin_dir_url(__FILE__)?>/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="<?= plugin_dir_url(__FILE__)?>/js/dataTables.responsive.min.js"></script>

<script>
    jQuery(window).load(function() {
        jQuery('#form-table').DataTable({
            'length': 10,
            "paging": true,
            responsive: false,
            "bAutoWidth": true
        })
    })
</script>

<?php  
}