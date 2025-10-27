<?php
namespace WP_Path_Dispatch\Tests\Feature;

use WP_Path_Dispatch\Path_Dispatch;
use WP_Path_Dispatch\Tests\TestCase;

use function Mantle\Support\Helpers\terminate_request;
use function WP_Path_Dispatch\Path_Dispatch;

/**
 * Visit {@see https://mantle.alley.co/testing/test-framework.html} to learn more.
 */
class PathDispatchTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		Path_Dispatch::clear_instance();

		$instance = Path_Dispatch();

		$instance->basic_paths   = [];
		$instance->rewrite_paths = [];

		flush_rewrite_rules();
	}

	public function test_add_simple_path() {
		Path_Dispatch()->add_path(
			[
				'path'     => 'some-path',
				'callback' => function () {
					echo 'some-response';
					terminate_request();
				},
			]
		);

		$this->register_rules();

		$this->get( '/some-path/' )
			->assertStatus( 200 )
			->assertContent( 'some-response' );
	}

	public function test_add_multiple_paths() {
		Path_Dispatch()->add_paths(
			[
				[
					'path'     => 'some-path',
					'callback' => function () {
						echo 'some-response';
						terminate_request();
					},
				],
				[
					'path'     => 'some-other-path',
					'callback' => function () {
						echo 'some-other-response';
						terminate_request();
					},
				],
			]
		);

		$this->register_rules();

		$this->get( '/some-path/' )
			->assertStatus( 200 )
			->assertContent( 'some-response' );

		$this->get( '/some-other-path/' )
			->assertStatus( 200 )
			->assertContent( 'some-other-response' );
	}

	public function test_action_path() {
		$this->expectApplied( 'some_action' )->once();

		Path_Dispatch()->add_path(
			[
				'action' => 'some_action',
				'path'   => 'some-path',
			]
		);

		$this->register_rules();

		$this->get( '/some-path/' );
	}

	public function test_custom_rewrite_path() {
		Path_Dispatch()->add_path(
			[
				'path'     => 'some-path',
				'rewrite'  => [
					'rule' => 'example/(.*)/?',
					'redirect' => 'index.php?dispatch=some-path&some_query_var=$matches[1]',
					'query_vars' => [ 'some_query_var' ],
				],
				'callback' => function () {
					echo 'some-response: ' . get_query_var( 'some_query_var' );
					terminate_request();
				},
			]
		);

		$this->register_rules();

		$this->get( '/example/foo/' )
			->assertOk()
			->assertContent( 'some-response: foo' );
	}

	public function test_template_path(): void {
		// Fake the template part being loaded.
		add_action(
			'get_template_part',
			function (): void {
				echo 'This is a template path response.';
			}
		);

		Path_Dispatch()->add_path(
			[
				'path'     => 'some-path',
				'template' => 'some-template',
			]
		);

		$this->register_rules();

		$this->get( '/some-path/' )
			->assertOk()
			->assertContent( 'This is a template path response.' );
	}

	protected function register_rules() {
		Path_Dispatch()->add_rewrite_rules();

		flush_rewrite_rules();
	}
}
