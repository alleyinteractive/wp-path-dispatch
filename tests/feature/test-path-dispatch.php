<?php
namespace WP_Path_Dispatch\Tests\Feature;

use WP_Path_Dispatch\Path_Dispatch;
use WP_Path_Dispatch\Tests\Test_Case;

use function WP_Path_Dispatch\Path_Dispatch;

/**
 * Visit {@see https://mantle.alley.co/testing/test-framework.html} to learn more.
 */
class Path_Dispatch_Test extends Test_Case {
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
				},
			]
		);

		$this->register_rules();

		$this->get( '/some-path/' )
			->assertStatus( 200 )
			->assertSee( 'some-response' );
	}

	public function test_add_multiple_paths() {
		Path_Dispatch()->add_paths(
			[
				[
					'path'     => 'some-path',
					'callback' => function () {
						echo 'some-response';
					},
				],
				[
					'path'     => 'some-other-path',
					'callback' => function () {
						echo 'some-other-response';
					},
				],
			]
		);

		$this->register_rules();

		$this->get( '/some-path/' )
			->assertStatus( 200 )
			->assertSee( 'some-response' );

		$this->get( '/some-other-path/' )
			->assertStatus( 200 )
			->assertSee( 'some-other-response' );
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
				},
			]
		);

		$this->register_rules();

		$this->get( '/example/foo/' )
			->assertOk()
			->assertSee( 'some-response: foo' );
	}

	protected function register_rules() {
		Path_Dispatch()->add_rewrite_rules();

		flush_rewrite_rules();
	}
}
