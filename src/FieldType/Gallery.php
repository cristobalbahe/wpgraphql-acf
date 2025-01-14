<?php
namespace WPGraphQL\Acf\FieldType;

use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use WPGraphQL\Acf\AcfGraphQLFieldType;
use WPGraphQL\Acf\FieldConfig;

class Gallery {

	/**
	 * @return void
	 */
	public static function register_field_type(): void {
		register_graphql_acf_field_type(
			'gallery',
			[
				'exclude_admin_fields' => [ 'graphql_non_null' ],
				'graphql_type'         => static function ( FieldConfig $field_config, AcfGraphQLFieldType $acf_field_type ) {
					if ( empty( $field_config->get_graphql_field_group_type_name() ) || empty( $field_config->get_graphql_field_name() ) ) {
						return null;
					}

					$type_name = $field_config->get_graphql_field_group_type_name();
					$to_type   = 'MediaItem';

					$field_config->register_graphql_connections(
						[
							'description'           => $field_config->get_field_description(),
							'acf_field'             => $field_config->get_acf_field(),
							'acf_field_group'       => $field_config->get_acf_field_group(),
							'fromType'              => $type_name,
							'toType'                => $to_type,
							'fromFieldName'         => $field_config->get_graphql_field_name(),
							'oneToOne'              => false,
							'allowFieldUnderscores' => true,
							'resolve'               => static function ( $root, $args, AppContext $context, $info ) use ( $field_config ) {
								$value = $field_config->resolve_field( $root, $args, $context, $info );


								if ( empty( $value ) || ! is_array( $value ) ) {
									return null;
								}

								$value = array_map(
									static function ( $id ) {
										return absint( $id );
									},
									$value
								);

								$resolver = new PostObjectConnectionResolver( $root, $args, $context, $info, 'attachment' );
								return $resolver
								->set_query_arg( 'post__in', $value )
								->set_query_arg( 'orderby', 'post__in' )
								->get_connection();
							},
						]
					);

					return 'connection';
				},
			]
		);
	}

}
