<?php
namespace PHPHtmlParser\Dom;

/**
 * This mock object is used solely for testing the abstract
 * class Node with out any potential side effects caused
 * by testing a supper class of Node.
 *
 * This object is not to be used for any other reason.
 */
class MockNode extends AbstractNode {

	public function innerHtml() {}

	public function outerHtml() {}

	public function text() {}

	protected function clear() {}
}
