<?php declare(strict_types=1);

/**
 * Based on code released under a BSD-style license. For complete license text
 * see http://sgraastra.net/code/license/.
 */

namespace StudyPortals\Template;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */

class Condition extends NodeTree
{

    /**
     * @var string $condition
     */
    protected $condition;

    /**
     * @var string $operator
     */

    protected $operator;

    /**
     * @var mixed $value
     */

    protected $value;

    /**
     * @var array<string> $value_set
     */

    protected $value_set = [];

    /**
     * Construct a new Condition Node.
     *
     * The contents of this Node will be displayed based upon a condition
     * evaluated at runtime. The {@link $condition}, {@link $operator} and
     * {@link value} parameters define the condition to be evaluated. The
     * {@link $condition} parameter contains the name of the value queried from
     * the Template tree.
     *
     * @param NodeTree $Parent
     * @param string $condition
     * @param string $operator
     * @param string|array<string> $value
     * @throws TemplateException
     */

    public function __construct(
        NodeTree $Parent,
        string $condition,
        string $operator,
        $value
    ) {

        if (!$this->isValidName($condition)) {
            throw new TemplateException(
                "Invalid condition \"$condition\" specified for Condition node"
            );
        }

        parent::__construct($Parent);

        $this->condition = $condition;
        $this->operator = $operator;

        // Set-values

        if ($this->operator == 'in' || $this->operator == '!in') {
            if (!is_array($value)) {
                throw new TemplateException(
                    "Invalid set-value specified for Condition node \"$condition\""
                );
            }

            $this->value_set = $value;
            return;
        }

        // Scalar-values

        $this->value = $value;
    }

    /**
     * Execute the comparison stored in this Node on the provided value.
     *
     * @param mixed $value
     * @return boolean
     */

    public function compareValue($value)
    {

        switch ($this->operator) {
            // Scalar

            case '==':
                return $value == $this->value;
            case '!=':
                return $value != $this->value;
            case '<':
                return $value < $this->value;
            case '<=':
                return $value <= $this->value;
            case '>':
                return $value > $this->value;
            case '>=':
                return $value >= $this->value;

            // Set

            case 'in':
            case '!in':
                $match = ($this->operator == 'in' ? false : true);

                foreach ($this->value_set as $element) {
                    if ($value == $element) {
                        $match = ($this->operator == 'in' ? true : false);
                        break;
                    }
                }

                return $match;

            default:
                throw new TemplateException(
                    "Unknown comparison operator {$this->operator} encountered"
                );
        }
    }

    /**
     * Display the contents of the condition node.
     *
     * @return string
     * @see NodeTree::display()
     */

    public function display(): string
    {
        $value = null;

        if ($this->Parent instanceof NodeTree) {
            $value = $this->Parent->getValue($this->condition);
        }

        if ($value === null && $this->getRoot()->isStrict()) {
            throw new StrictException(
                "Condition \"{$this->condition}\" has not been set"
            );
        }


        if (!$this->compareValue($value)) {
            return '';
        }

        return parent::display();
    }
}
