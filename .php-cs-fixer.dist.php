<?php
$finder = PhpCsFixer\Finder::create()
	->in(__DIR__);

return (new PhpCsFixer\Config())
	->setRules([
		'control_structure_braces' => true,
		'braces' => [
			'allow_single_line_closure' => false,
			'position_after_control_structures' => 'next',
			'position_after_functions_and_oop_constructs' => 'next',
			'position_after_anonymous_constructs' => 'next',
		],
		'indentation_type' => true,
		'elseif' => false,
		'binary_operator_spaces' => ['default' => 'single_space'],
		'cast_spaces' => ['space' => 'single'],
		'no_spaces_after_function_name' => true,
		'no_spaces_inside_parenthesis' => true,
		'ternary_operator_spaces' => true,
		'single_quote' => true,
		'switch_case_semicolon_to_colon' => true,
		'switch_case_space' => true,
		'blank_line_before_statement' => ['statements' => ['break', 'return', 'switch', 'throw', 'try']],
		'array_syntax' => ['syntax' => 'long'],
		'echo_tag_syntax' => ['format' => 'long'],
		'no_trailing_comma_in_list_call' => true,
		'lowercase_keywords' => true,
		'lowercase_static_reference' => true,
		'lowercase_cast' => true,
		'constant_case' => ['case' => 'lower'],
		'magic_constant_casing' => true,
		'magic_method_casing' => true,
		'native_function_casing' => true,
		'native_function_type_declaration_casing' => true,
		'line_ending' => true,
		'no_trailing_whitespace' => true,
		'no_whitespace_in_blank_line' => true,
		'single_blank_line_at_eof' => true,
		'encoding' => true,
		'full_opening_tag' => true,
		'class_definition' => ['single_line' => false],
	])
	->setFinder($finder)
	->setIndent("\t")
	->setLineEnding("\n");
