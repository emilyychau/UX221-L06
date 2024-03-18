<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @author        impleCode
 *
 */


/**
 * Utility class to generate HTML tags
 *
 */
class ic_html_util {
	/**
	 * @var int
	 */
	private $counter = 0;

	/**
	 * @var string
	 */
	private $class_prefix = 'ic-';

	/**
	 * @var bool
	 */
	public $fix_id = true;

	/**
	 * @param $label
	 * @param $class
	 *
	 * @return string
	 */
	function button( $label, $class, $onclick = null, $attr = array() ) {
		$class = 'button ' . design_schemes( 'box', 0 ) . ' ' . $class;
		if ( ! empty( $onclick ) ) {
			$attr['onclick'] = $onclick;
		}

		return $this->div( $label, $class, null, $attr );
	}

	/**
	 * @param $buttons
	 *
	 * @return string
	 */
	function buttons( $buttons ) {
		if ( empty( $buttons ) ) {
			return '';
		}
		$button_tags = '';
		foreach ( $buttons as $button ) {
			if ( ! empty( $button['for'] ) ) {
				$class = 'button ' . design_schemes( 'box', 0 ) . ' ' . $button['class'];
				if ( ! empty( $button['input'] ) ) {
					$button['label'] .= $button['input'];
				}
				$button_tags .= $this->label( $button['label'], $button['for'], $class );
			} else if ( ! empty( $button['url'] ) ) {
				$class       = 'button ' . design_schemes( 'box', 0 ) . ' ' . $button['class'];
				$button_tags .= $this->link( $button['label'], $class, $button['url'] );
			} else {
				$onclick     = isset( $button['onclick'] ) ? $button['onclick'] : '';
				$button_tags .= $this->button( $button['label'], $button['class'], $onclick );
			}
		}

		return $this->div( $button_tags, 'ic-buttons' );
	}

	/**
	 * @param $label
	 * @param $class
	 * @param $url
	 *
	 * @return string
	 */
	function link( $label, $class, $url ) {
		$attr = array(
			'href'  => $url,
			'class' => $class,
		);

		return $this->tag( 'a', $label, $attr );
	}

	/**
	 * @param $content
	 * @param $class
	 *
	 * @return string
	 */
	function div( $content, $class = null, $id = null, $attr = array() ) {
		if ( ! empty( $class ) ) {
			$attr['class'] = $class;
		}
		if ( ! empty( $id ) ) {
			$attr['id'] = $id;
		}

		return $this->tag( 'div', $content, $attr );

	}

	function popup( $inside, $buttons, $class = null ) {
		$class_base = 'ic-modal-container';
		$id         = '';
		if ( ! empty( $class ) ) {
			$id    = $class;
			$class = ' ' . $class;
		}
		$inside_container = $this->div( $inside, $class_base . '-inside', $class_base . '-inside' );
		if ( ! empty( $buttons ) ) {
			$buttons_container = $this->div( $this->buttons( $buttons ), $class_base . '-buttons' );
		} else {
			$buttons_container = '';
		}
		$container = $this->div( $inside_container . $buttons_container, $class_base );

		return $this->div( $container, $class_base . '-container ic-overlay-container' . $class, $id );
	}

	/**
	 * @param $name
	 * @param $fields array of fields with name, label, comment, required, type, value
	 * @param $buttons
	 * @param string $action
	 * @param string $method
	 * @param bool $p
	 *
	 * @return string
	 */
	function form( $form_name, $form_title, $fields, $buttons = '', $action = '', $method = 'post', $p = true, $before = '', $show_title = true, $ajax_data = array() ) {
		$default_attr = array();
		$class        = 'ic-form ' . $form_name;
		if ( empty( $buttons ) ) {
			$class                 .= ' ic_ajax';
			$default_attr['class'] = 'ic_self_submit';
		}
		$attr = array(
			'name'   => $form_name,
			'id'     => $form_name,
			'class'  => $class,
			'action' => $action,
			'method' => $method
		);
		if ( empty( $buttons ) ) {
			$attr['data-ic_ajax'] = $form_name;
		}
		$content = $before;
		if ( ! empty( $form_title ) ) {
			$attr['data-ic_responsive_label'] = $form_title;
			if ( $show_title ) {
				$content .= $this->p( $form_title, 'ic-form-title' );
			}
		}
		if ( ! empty( $ajax_data ) ) {
			$attr['data-ic_ajax_data'] = json_encode( $ajax_data );
		}

		foreach ( $fields as $key => $field ) {
			$field_name = $form_name . '-' . $field['name'];
			$field_id   = $field_name . '_' . $key;
			$label      = isset( $field['label'] ) ? $field['label'] : '';
			$comment    = isset( $field['comment'] ) ? $field['comment'] : '';
			$required   = isset( $field['required'] ) ? $field['required'] : '';
			$options    = isset( $field['options'] ) ? $field['options'] : array();

			$content .= $this->input( $field['type'], $field_name, $field['value'], $field_id, $required, $label, $comment, $p, $options, $default_attr );
		}
		if ( ! empty( $buttons ) ) {
			$content .= $this->buttons( $buttons );
		}
		$content .= '<input class="ic-hidden-submit" style="display: none;" type="submit">';

		return $this->tag( 'form', $this->div( $content, 'ic-form-inside' ), $attr );
	}

	/**
	 * @param $type
	 * @param $name
	 * @param $value
	 * @param $id
	 * @param null $label
	 * @param false $p
	 *
	 * @return string
	 */
	function input( $type, $name, $value, $id = '', $required = 0, $label = null, $comment = null, $p = false, $options = array(), $default_attr = array() ) {
		$return = '';
		$attr   = array_merge( array(
			'type'  => $type,
			'id'    => $this->fix_id ? str_replace( '-', '_', $id ) : $id,
			'name'  => $this->fix_id ? str_replace( '_', '-', $name ) : $name,
			'value' => $value
		), $default_attr );
		if ( ! empty( $required ) ) {
			$attr['required'] = 'required';
		}
		if ( ! empty( $label ) && ( ! ( $type === 'radio' || $type === 'checkbox' ) || ! empty( $options ) ) ) {
			$return .= $this->label( $label, $attr['id'] );
		}

		if ( ! empty( $options ) ) {
			if ( $type === 'dropdown' ) {
				$dropdown_options = $this->dropdown_options( $options, $value );
				if ( ! empty( $dropdown_options ) ) {
					unset( $attr['type'] );
					unset( $attr['value'] );
					$return .= $this->tag( 'select', $dropdown_options, $attr );
				}
			} else if ( $type === 'radio' || $type === 'checkbox' ) {
				$attr['class'] = isset( $attr['class'] ) ? $attr['class'] : '';
				$return        .= $this->radio_checkbox_options( $options, $value, $name, $attr['id'], $required, $type, $attr['class'] );
			}
		} else {
			if ( $type === 'checkbox' ) {
				if ( empty( $attr['checked'] ) && $attr['value'] === '1' ) {
					$attr['checked'] = 'checked';
				}
				if ( empty( $attr['value'] ) ) {
					$attr['value'] = '1';
				}
			}
			$return .= $this->tag( 'input', '', $attr );
		}
		if ( ( $type === 'radio' || $type === 'checkbox' ) && ! empty( $label ) && empty( $options ) ) {
			$return .= $this->label( $label, $attr['id'] );
		}
		if ( ! empty( $comment ) ) {
			$return .= $this->span( $comment, 'ic-input-comment' );
		}
		if ( $p && $type !== 'hidden' && empty( $options ) ) {
			$class  = sanitize_title( $name );
			$class  .= ' ' . $this->class_prefix . $attr['type'] . '-container';
			$return = $this->p( $return, $class );
		}

		return $return;
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $id
	 * @param $options
	 * @param $required
	 * @param $label
	 * @param $comment
	 * @param $p
	 *
	 * @return string
	 */
	function dropdown( $name, $value, $id, $options, $required = 0, $label = null, $comment = null, $p = false ) {
		return $this->input( 'dropdown', $name, $value, $id, $required, $label, $comment, $p, $options, array() );
	}

	/**
	 * @param $options
	 * @param $selected_value
	 *
	 * @return string
	 */
	function dropdown_options( $options, $selected_value = null ) {
		$dropdown_options = '';
		foreach ( $options as $option_value => $option_label ) {
			$selected = false;
			if ( $selected_value === $option_value ) {
				$selected = true;
			}
			$options_attr = array( 'value' => $option_value );
			if ( $selected ) {
				$options_attr['selected'] = 'selected';
			}
			$dropdown_options .= $this->tag( 'option', $option_label, $options_attr );
		}

		return $dropdown_options;
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $label
	 * @param $selected
	 * @param $id
	 * @param $required
	 *
	 * @return void
	 */
	function radio( $name, $value, $label, $selected = false, $id = '', $required = 0, $class = '' ) {
		$attr = array( 'checked' => $selected );
		if ( ! empty( $class ) ) {
			$attr['class'] = $class;
		}

		return $this->input( 'radio', $name, $value, $id, $required, $label, null, true, array(), $attr );
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $label
	 * @param $selected
	 * @param $id
	 * @param $required
	 *
	 * @return void
	 */
	function checkbox( $name, $value, $label, $selected = false, $id = '', $required = 0, $class = '' ) {
		$attr = array( 'checked' => $selected );
		if ( ! empty( $class ) ) {
			$attr['class'] = $class;
		}

		return $this->input( 'checkbox', $name, $value, $id, $required, $label, null, true, array(), $attr );
	}

	/**
	 * @param $options
	 * @param $selected_value
	 * @param $name
	 * @param $id
	 * @param $required
	 * @param $type
	 *
	 * @return string
	 */
	function radio_checkbox_options( $options, $selected_value, $name, $id = '', $required = 0, $type = 'radio', $class = '' ) {
		$radio_checkbox_options = '';
		$name_array_base        = false;
		if ( ! ic_string_contains( $name, '[' ) ) {
			$name_array_base = $name;
		}
		foreach ( $options as $option_value => $option_label ) {
			$option_id = $id . sanitize_title( $option_value );
			$selected  = false;
			if ( $selected_value == $option_value ) {
				$selected = true;
			}
			if ( $type === 'radio' ) {
				$radio_checkbox_options .= $this->radio( $name, $option_value, $option_label, $selected, $option_id, $required, $class );
			} else if ( $type === 'checkbox' ) {
				if ( $name_array_base ) {
					$name = $name_array_base . '[]';
					if ( is_array( $selected_value ) && in_array( $option_value, $selected_value ) ) {
						$selected = true;
					}
				}
				$radio_checkbox_options .= $this->checkbox( $name, $option_value, $option_label, $selected, $option_id, $required, $class );
			}
		}

		return $radio_checkbox_options;
	}

	/**
	 * @param $label
	 * @param $id
	 *
	 * @return string
	 */
	function label( $label, $id = '', $class = '' ) {
		$type = 'label';
		$attr = array();
		if ( ! empty( $id ) ) {
			$attr['for'] = $id;
		}
		if ( ! empty( $class ) ) {
			$attr['class'] = $class;
		}

		return $this->tag( $type, $label, $attr );
	}

	/**
	 * @param $content
	 * @param null $class
	 *
	 * @return string
	 */
	function p( $content, $class = null ) {
		$type = 'p';
		if ( ! empty( $class ) ) {
			$attr = array( 'class' => $class );
		} else {
			$attr = array();
		}

		return $this->tag( $type, $content, $attr );
	}

	/**
	 * @param $content
	 * @param $class
	 *
	 * @return string
	 */
	function span( $content, $class = null ) {
		$type = 'span';
		if ( ! empty( $class ) ) {
			$attr = array( 'class' => $class );
		} else {
			$attr = array();
		}

		return $this->tag( $type, $content, $attr );
	}

	/**
	 * @return string
	 */
	function br() {
		return $this->tag( 'br' );
	}

	/**
	 * @param array $list
	 * @param string $class
	 *
	 * @return string
	 */
	function ul( $list, $class = null ) {
		if ( empty( $list ) || ! is_array( $list ) ) {
			return '';
		}
		$ul = '';
		foreach ( $list as $li ) {
			if ( empty( $li ) ) {
				continue;
			}
			$ul .= $this->tag( 'li', $li );
		}
		if ( ! empty( $ul ) ) {
			if ( ! empty( $class ) ) {
				$attr = array( 'class' => $class );
			} else {
				$attr = array();
			}

			return $this->tag( 'ul', $ul, $attr );
		} else {
			return '';
		}
	}

	/**
	 * @param $trs
	 * @param $class
	 *
	 * @return string
	 */
	function table( $trs, $class = null, $ths = array() ) {
		$rows = '';
		if ( ! empty( $ths ) ) {
			$rows .= $this->tr( $ths );
		}
		foreach ( $trs as $tr ) {
			$tr['attr'] = isset( $tr['attr'] ) ? $tr['attr'] : array();
			$rows       .= $this->tr( $tr['tds'], $tr['class'], $tr['attr'] );
		}
		if ( ! empty( $rows ) ) {
			return $this->tag( 'table', $rows, array( 'class' => $class ) );
		} else {
			return '';
		}
	}

	/**
	 * @param $tds
	 * @param $class
	 *
	 * @return string
	 */
	function tr( $tds, $class = null, $attr = array() ) {
		$tr = '';
		foreach ( $tds as $td ) {
			if ( ! empty( $td['header'] ) ) {
				$td['attr'] = isset( $td['attr'] ) ? $td['attr'] : array();
				$tr         .= $this->th( $td['header'], $td['class'], $td['attr'] );
				continue;
			}
			if ( is_array( $td ) ) {
				$td['attr'] = isset( $td['attr'] ) ? $td['attr'] : array();
				$tr         .= $this->td( $td['content'], $td['class'], $td['attr'] );
			} else {
				$tr .= $this->td( $td );
			}
		}
		if ( ! empty( $tr ) ) {
			if ( ! empty( $class ) ) {
				$attr['class'] = $class;
			}

			return $this->tag( 'tr', $tr, $attr );
		} else {
			return '';
		}
	}

	/**
	 * @param $content
	 * @param $class
	 *
	 * @return string
	 */
	function td( $content, $class = null, $attr = array() ) {
		if ( ! empty( $class ) ) {
			$attr['class'] = $class;
		}

		return $this->tag( 'td', $content, $attr );
	}

	/**
	 * @param $content
	 * @param $class
	 *
	 * @return string
	 */
	function th( $content, $class = null, $attr = array() ) {
		if ( ! empty( $class ) ) {
			$attr['class'] = $class;
		}

		return $this->tag( 'th', $content, $attr );
	}

	/**
	 * @param $type
	 * @param $content
	 * @param array $attr
	 *
	 * @return string
	 */
	function tag( $type, $content = '', $attr = array() ) {
		if ( $this->fix_id && ! empty( $attr['id'] ) ) {
			$this->counter ++;
			$attr['id'] = sanitize_title( str_replace( '-', '_', $attr['id'] ) . '_' . $this->counter );
		}
		$tag = '<' . $type; // Open tag
		if ( empty( $attr['class'] ) ) {
			$attr['class'] = '';
		} else {
			$attr['class'] .= ' ';
		}
		$attr['class'] .= $this->class_prefix . $type;
		if ( ! empty( $attr['type'] ) ) {
			$attr['class'] .= ' ' . $this->class_prefix . $attr['type'];
		}
		foreach ( $attr as $name => $value ) {
			if ( is_bool( $value ) && ! $value ) {
				continue;
			}
			if ( ic_string_contains( $value, ' ' ) && $name !== 'value' && $name !== 'onclick' && ! ic_string_contains( $name, 'data-' ) && substr( $value, 0, 1 ) !== '{' ) {
				$value = implode( ' ', array_map( 'sanitize_title', explode( ' ', $value ) ) );
			}
			$tag .= ' ';
			$tag .= sanitize_title( $name ) . '="' . esc_attr( $value ) . '"';
		}
		$tag .= '>'; // Close tag opener
		$tag .= $content;
		$tag .= $this->tag_closer( $type ); // Close tag

		return $tag;
	}

	/**
	 * @param $type
	 *
	 * @return string
	 */
	function tag_closer( $type ) {
		$no_closer = array(
			'input',
			'br'
		);
		if ( in_array( $type, $no_closer ) ) {
			return '';
		}

		return '</' . $type . '>';
	}
}