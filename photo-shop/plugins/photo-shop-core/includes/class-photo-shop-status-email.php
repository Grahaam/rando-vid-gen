<?php
/**
 * Customer email sent when an order reaches a custom status.
 *
 * @package PhotoShopCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Generic status email, reusing WooCommerce's own customer email template.
 */
class Photo_Shop_Status_Email extends WC_Email {

	/**
	 * Intro text shown above the order details.
	 *
	 * @var string
	 */
	protected $intro;

	/**
	 * Default subject.
	 *
	 * @var string
	 */
	protected $default_subject;

	/**
	 * Default heading.
	 *
	 * @var string
	 */
	protected $default_heading;

	/**
	 * Constructor.
	 *
	 * @param string $status  Status slug without "wc-".
	 * @param string $title   Title in WooCommerce > Settings > Emails.
	 * @param string $subject Default subject.
	 * @param string $heading Default heading.
	 * @param string $intro   Intro paragraph.
	 */
	public function __construct( $status, $title, $subject, $heading, $intro ) {
		$this->id             = 'customer_' . $status . '_order';
		$this->customer_email = true;
		$this->title          = $title;
		/* translators: %s: order status */
		$this->description     = sprintf( __( 'Sent to the customer when an order is marked %s.', 'photo-shop-core' ), $status );
		$this->template_html   = 'emails/customer-completed-order.php';
		$this->template_plain  = 'emails/plain/customer-completed-order.php';
		$this->placeholders    = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);
		$this->default_subject = $subject;
		$this->default_heading = $heading;
		$this->intro           = $intro;

		add_action( 'woocommerce_order_status_' . $status . '_notification', array( $this, 'trigger' ), 10, 2 );

		parent::__construct();
	}

	/**
	 * Default subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return $this->default_subject;
	}

	/**
	 * Default heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return $this->default_heading;
	}

	/**
	 * Default additional content, editable in the email settings.
	 *
	 * @return string
	 */
	public function get_default_additional_content() {
		return $this->intro;
	}

	/**
	 * Send the email.
	 *
	 * @param int           $order_id Order ID.
	 * @param WC_Order|bool $order    Order object.
	 */
	public function trigger( $order_id, $order = false ) {
		$this->setup_locale();

		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object                         = $order;
			$this->recipient                      = $order->get_billing_email();
			$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
			$this->placeholders['{order_number}'] = $order->get_order_number();
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * HTML content.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
			)
		);
	}

	/**
	 * Plain text content.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
			)
		);
	}
}
