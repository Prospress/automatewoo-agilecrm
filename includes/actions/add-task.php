<?php
/**
 * @class       AW_Action_AgileCRM_Add_Task
 * @package     AutomateWoo/Addons/AgileCRM
 * @since       1.0.0
 */

class AW_Action_AgileCRM_Add_Task extends AW_Action_AgileCRM_Abstract
{
	public $name = 'agilecrm_add_task';

	/**
	 * Init
	 */
	public function init()
	{
		$this->title = __('Add Task To Contact', 'automatewoo-agilecrm');
		parent::init();
	}


	public function load_fields()
	{
		$name = ( new AW_Field_Text_Input() )
			->set_name('subject')
			->set_title( __( 'Task Name', 'automatewoo-agilecrm' ) )
			->set_required();

		$type = ( new AW_Field_Select( false ) )
			->set_name('type')
			->set_title( __( 'Task Type', 'automatewoo-agilecrm' ) )
			->set_options([
				'CALL' => 'Call',
				'EMAIL' => 'Email',
				'FOLLOW_UP' => 'Follow Up',
				'MEETING' => 'Meeting',
				'MILESTONE' => 'Milestone',
				'SEND' => 'Send',
				'TWEET' => 'Tweet',
				'OTHER' => 'Other'
			])
			->set_required();

		$priority = ( new AW_Field_Select( false ) )
			->set_name('priority')
			->set_title( __( 'Priority', 'automatewoo-agilecrm' ) )
			->set_default( 'NORMAL' )
			->set_options([
				'HIGH' => 'High',
				'NORMAL' => 'Normal',
				'LOW' => 'Low'
			])
			->set_required();

		$due = ( new AW_Field_Text_Input() )
			->set_name('due')
			->set_title( __( 'Due', 'automatewoo-agilecrm' ) )
			->set_placeholder('e.g. {{ shop.current_datetime | modify : +1 day }}')
			->set_required();

		$description = ( new AW_Field_Text_Area() )
			->set_name('description')
			->set_title( __( 'Description', 'automatewoo-agilecrm' ) )
			->set_rows( 3 );

		$this->add_contact_email_field();
		$this->add_field( $name );
		$this->add_field( $type );
		$this->add_field( $priority );
		$this->add_field( $due );
		$this->add_field( $description );
	}


	/**
	 * @return void
	 */
	public function run()
	{
		$email = aw_clean_email( $this->get_option( 'email', true ) );
		$subject = aw_clean( $this->get_option( 'subject', true ) );
		$type = aw_clean( $this->get_option( 'type' ) );
		$priority = aw_clean( $this->get_option( 'priority' ) );
		$due = aw_clean( $this->get_option( 'due', true ) );
		$description = aw_clean( $this->get_option( 'description', true ) );

		if ( empty( $subject ) || empty( $email ) || ! AW_AgileCRM()->api() )
			return;

		$contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $email );

		if ( $contact_id )
		{
			// convert to gmt timestamp

			if ( ! $due = strtotime( get_gmt_from_date( $due ) ) )
			{
				$due = time();
			}

			// add tags
			$response = AW_AgileCRM()->api()->request( 'POST', '/tasks', [
				'contacts' => [ $contact_id ],
				'subject' => $subject,
				'type' => $type,
				'priority' => $priority,
				'due' => $due,
				'taskDescription' => $description,
			]);
		}

	}

}
