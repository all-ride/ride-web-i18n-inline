<?php

namespace ride\library\html\table;

use ride\library\form\component\Component;
use ride\library\form\Form;
use ride\library\form\FormBuilder;
use ride\library\html\exception\TableException;
use ride\library\html\exception\TableNotProcessedException;
use ride\library\html\table\decorator\Decorator;
use ride\library\html\table\export\ExportFormat;
use ride\library\html\Pagination;
use ride\library\reflection\Callback;

/**
 * Table implementation with search, order and pagination functionality
 */
class FormTable extends ArrayTable implements ExportTable, Component {

    /**
     * Name of the field for the table name
     * @var string
     */
    const FIELD_NAME = 'name';

    /**
     * Name of the field for the row id's
     * @var string
     */
    const FIELD_ID = 'id';

    /**
     * Name of the action field
     * @var string
     */
    const FIELD_ACTION = 'action';

    /**
     * Name of the search query field
     * @var string
     */
    const FIELD_SEARCH_QUERY = 'search-query';

    /**
     * Name of the field with the number of rows per page
     * @var string
     */
    const FIELD_PAGE_ROWS = 'page-rows';

    /**
     * Name of the order field
     * @var string
     */
    const FIELD_ORDER_METHOD = 'order-method';

    /**
     * Ascending order direction identifier
     * @var string
     */
    const ORDER_DIRECTION_ASC = 'asc';

    /**
     * Descending order direction identifier
     * @var string
     */
    const ORDER_DIRECTION_DESC = 'desc';

    /**
     * URL for the form of this table
     * @var string
     */
    protected $formUrl;

    /**
     * Flag to set whether this form has been processed or not
     * @var boolean
     */
    protected $isProcessed;

    /**
     * Total number of rows in this table (ignores the pagination)
     * @var integer
     */
    protected $countRows;

    /**
     * Array with the label for the action as key and the callback to the
     * action as value
     * @var array
     */
    protected $actions;

    /**
     * Array with the label of the action as key and the confirmation message
     * for the action as value
     * @var array
     */
    protected $actionConfirmationMessages;

    /**
     * Array with the label of the order method as key and a OrderMethod object
     * as value
     * @var array
     */
    protected $orderMethods;

    /**
     * Label of the current order method
     * @var string
     */
    protected $orderMethod;

    /**
     * Current order direction
     * @var string
     */
    protected $orderDirection;

    /**
     * URL for the order direction
     * @var string
     */
    protected $orderUrl;

    /**
     * Number of rows per page
     * @var integer
     */
    protected $pageRows;

    /**
     * Array with the different options for rows per page
     * @var array
     */
    protected $paginationOptions;

    /**
     * URL for the pagination
     * @var string
     */
    protected $paginationUrl;

    /**
     * Number of the current page
     * @var integer
     */
    protected $page;

    /**
     * Number of pages
     * @var integer
     */
    protected $pages;

    /**
     * Flag to seth whether the applySearch method is implemented
     * @var boolean
     */
    protected $hasSearch;

    /**
     * Search query submitted by the form
     * @var string
     */
    protected $searchQuery;

    /**
     * Flag to set whether the export has been processed or not
     * @var boolean
     */
    protected $isExportProcessed;

    /**
     * Array with the value and header decorators for the decorators
     * @var array
     */
    protected $exportColumnDecorators;

    /**
     * Array the group decorators for the export
     * @var array
     */
    protected $exportGroupDecorators;

    /**
     * Constructs a new form table
     * @param array $values Values for the table
     * @param string $formAction URL where the table form will point to
     * @return null
     */
    public function __construct(array $values) {
        parent::__construct($values);

        $this->isProcessed = false;

        $this->hasSearch = false;
        $this->searchQuery = null;

        $this->actions = array();
        $this->actionConfirmationMessages = array();

        $this->orderDirection = self::ORDER_DIRECTION_ASC;
        $this->orderMethod = null;
        $this->orderMethods = array();

        $this->page = 1;
        $this->pages = 1;

        $this->isExportProcessed = false;
        $this->exportColumnDecorators = array();
        $this->exportGroupDecorators = array();
    }

    /**
     * Sets the URL for the form
     * @param string $url
     * @return null
     */
    public function setFormUrl($url) {
        $this->formUrl = $url;
    }

    /**
     * Gets the URL for the order direction
     * @return string
     */
    public function getFormUrl() {
        return $this->formUrl;
    }

    /**
     * Gets the number of rows set to this table
     * @return integer Number of rows
     */
    public function countRows() {
        return $this->countRows;
    }

    /**
     * Gets the number of rows set to the current page of this table
     * @return integer Number of rows on the current page
     */
    public function countPageRows() {
        if (!$this->isProcessed) {
            throw new TableException('Table is not processed, call processForm() first');
        }

        return parent::countRows();
    }

    /**
     * Adds an action to this table
     * @param string $label Label for the action
     * @param string|array|\ride\library\Callback $callback Callback to the
     * action
     * @param string $confirmationMessage Message for a confirmation dialog
     * before performing the action
     * @return null
     * @throws \ride\library\html\exception\TableException when the provided
     * label or confirmation message is empty or invalid
     */
    public function addAction($label, $callback, $confirmationMessage = null) {
        if ((!is_numeric($label) && !is_string($label)) || $label == '') {
            throw new TableException('Provided label for the action is empty or invalid');
        }

        $this->actions[$label] = new Callback($callback);

        if ($confirmationMessage === null) {
            return;
        }

        if ((!is_numeric($confirmationMessage) && !is_string($confirmationMessage)) || $confirmationMessage == '') {
            throw new TableException('Provided confirmation message for the action is empty or invalid');
        }

        $this->actionConfirmationMessages[$label] = $confirmationMessage;
    }

    /**
     * Gets all the confirmation messages for the actions
     * @return array Array with the label of the action as key and the
     * confirmation message as value
     */
    public function getActionConfirmationMessages() {
        return $this->actionConfirmationMessages;
    }

    /**
     * Gets whether this table has actions
     * @return boolean True when the table has action, false otherwise
     */
    public function hasActions() {
        return !empty($this->actions);
    }

    /**
     * Sets whether this table has the search field implemented
     * @param boolean $flag True if applySearch method is implemented, false
     * otherwise
     * @return null
     */
    protected function setHasSearch($flag) {
        $this->hasSearch = $flag;
    }

    /**
     * Gets whether this table has the search field implemented
     * @return boolean True if the applySearch method is implemented; false
     * otherwise
     */
    public function hasSearch() {
        return $this->hasSearch;
    }

    /**
     * Sets the search query for this table
     * @param string $query Search query
     * @return null
     * @throws \ride\library\html\exception\TableException when the search is
     * disabled on this table
     */
    public function setSearchQuery($query) {
        if (!$this->hasSearch()) {
            throw new TableException('Cannot set the search query: no search enabled on this table');
        }

        $this->searchQuery = $query;
    }

    /**
     * Gets the search query for this table
     * @return string
     */
    public function getSearchQuery() {
        return $this->searchQuery;
    }

    /**
     * Adds a new order method to the table. Provide extra arguments to pass
     * arguments to the order callbacks. The first argumen will always be the
     * values array
     * @param string $label Label for the order method
     * @param string|array|\ride\library\Callback $callbackAscending Callback
     * to order ascending
     * @param string|array|\ride\library\Callback $callbackDescending Callback
     * to order descending
     * @return null
     * @throws \ride\library\html\exception\TableException when the provided
     * label is empty or invalid
     */
    public function addOrderMethod($label, $callbackAscending, $callbackDescending) {
        if (!is_string($label) || $label == '') {
            throw new TableException('Provided label for the order method is empty');
        }

        $arguments = array_slice(func_get_args(), 3);

        $this->orderMethods[$label] = new OrderMethod($callbackAscending, $callbackDescending, $arguments);

        if (empty($this->orderMethod)) {
            $this->orderMethod = $label;
        }
    }

    /**
     * Gets whether this table has order methods
     * @return boolean True when order methods have been added, false otherwise
     */
    public function hasOrderMethods() {
        return !empty($this->orderMethods);
    }

    /**
     * Sets the current order method
     * @param string $label Label of the order method
     * @return null
     * @throws \ride\library\html\exception\TableException when the provided
     * label is not set as a order method
     */
    public function setOrderMethod($label) {
        if (!isset($this->orderMethods[$label])) {
            // throw new TableException('Provided label is not a set order method');
        }

        $this->orderMethod = $label;
    }

    /**
     * Gets the label of the current order method
     * @return string
     */
    public function getOrderMethod() {
        return $this->orderMethod;
    }

    /**
     * Sets the current order direction
     * @param string $direction Order direction
     * @return null
     * @throws \ride\library\html\exception\TableException when an invalid order
     * direction has been provided
     */
    public function setOrderDirection($direction) {
        $direction = strtolower($direction);
        if ($direction != self::ORDER_DIRECTION_ASC && $direction != self::ORDER_DIRECTION_DESC) {
            throw new TableException('Provided order direction is not valid , try ORDER_DIRECTION_ASC or ORDER_DIRECTION_DESC');
        }

        $this->orderDirection = $direction;
    }

    /**
     * Gets the current order direction
     * @return string
     */
    public function getOrderDirection() {
        return $this->orderDirection;
    }

    /**
     * Sets the URL for the order direction
     * @param string $url
     * @return null
     */
    public function setOrderDirectionUrl($url) {
        $this->orderUrl = $url;
    }

    /**
     * Gets the URL for the order direction
     * @return string
     */
    public function getOrderDirectionUrl() {
        return $this->orderUrl;
    }

    /**
     * Sets the options for rows per page
     * @param array $options Array with different rows per page values
     * @return null
     * @throws \ride\library\html\exception\TableException when an a negative
     * or invalid option is provided
     */
    public function setPaginationOptions(array $options = null) {
        if (!$options) {
            $this->paginationOptions = null;
            return;
        }

        $paginationOptions = array();
        foreach ($options as $option) {
            if (!is_numeric($option) || $option <= 0) {
                throw new TableException('Pagination rows option should be a positive integer');
            }

            $option = (integer) $option;

            $paginationOptions[$option] = $option;
        }

        $this->paginationOptions = $paginationOptions;
    }

    /**
     * Gets the options for rows per page
     * @return array Array with different rows per page values
     */
    public function getPaginationOptions() {
        return $this->paginationOptions;
    }

    /**
     * Gets whether the pagination options are set
     * @return boolean True if there are pagination options, false otherwise
     */
    public function hasPaginationOptions() {
        return $this->paginationOptions != null;
    }

    /**
     * Sets the URL for the pagination
     * @param string $url
     * @return null
     */
    public function setPaginationUrl($url) {
        $this->paginationUrl = $url;
    }

    /**
     * Gets the URL for the pagination
     * @return string
     */
    public function getPaginationUrl() {
        return $this->paginationUrl;
    }

    /**
     * Gets a pagination object from this table
     * @return \ride\library\html\Pagination
     */
    public function getPagination() {
        if (!$this->hasPaginationOptions()) {
            return null;
        }

        $pagination = new Pagination($this->getPages(), $this->getPage());
        $pagination->setHref($this->getPaginationUrl());

        return $pagination;
    }

    /**
     * Sets the number of rows per page
     * @param integer $rowsPerPage Number of rows per page
     * @return null
     * @throws \ride\library\html\exception\TableException when the provided
     * number of rows per page is invalid
     * @throws \ride\library\html\exception\TableException when the provided
     * number of rows per page is not available in the pagination options
     */
    public function setRowsPerPage($rowsPerPage) {
        if ($rowsPerPage === null) {
            $this->pageRows = null;
            $this->page = 1;
            return;
        }

        if (!is_numeric($rowsPerPage) || $rowsPerPage <= 0) {
            throw new TableException('Provided number of rows per page is not a positive integer');
        }

        $rowsPerPage = (integer) $rowsPerPage;

        if ($this->paginationOptions && !isset($this->paginationOptions[$rowsPerPage])) {
            throw new TableException('Provided number of rows per page is not available in the pagination options');
        }

        $this->pageRows = $rowsPerPage;
        $this->page = 1;
    }

    /**
     * Gets the number of rows per page
     * @return integer
     */
    public function getRowsPerPage() {
        return $this->pageRows;
    }

    /**
     * Sets the current page number
     * @param integer $page New page number
     * @return null
     * @throws \ride\library\html\exception\TableException when no pagination is
     * set
     * @throws \ride\library\html\exception\TableException when the provided
     * page number is invalid
     */
    public function setPage($page) {
        if ($this->pageRows === null) {
            throw new TableException('No pagination set, use setRowsPerPage first');
        }

        if (!is_numeric($page) || $page <= 0) {
            throw new TableException('Provided page number is not a positive number');
        }

        $this->page = (integer) $page;
    }

    /**
     * Gets the current page number
     * @return integer
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Gets the number of pages
     * @return integer
     */
    public function getPages() {
        if (!$this->isProcessed) {
            throw new TableException('Table is not processed, call processForm() first');
        }

        return $this->pages;
    }

    /**
     * Adds the decorators for a export column. A column decorator gets a
     * specific value from the table value and formats it for the column value.
     * @param \ride\library\html\table\decorator\Decorator $valueDecorator
     * Decorator to decorate the values of the table into a column
     * @param \ride\library\html\table\decorator\Decorator $headerDecorator
     * Decorator to decorate the header of the column
     * @return null
     */
    public function addExportDecorator(Decorator $valueDecorator, Decorator $headerDecorator) {
        $this->exportColumnDecorators[] = new ColumnDecorator($valueDecorator, $headerDecorator);
    }

    /**
     * Adds the group decorator to the table. Group decorators should return a
     * boolean to set whether to add the group row or not
     * @param \ride\library\html\table\decorator\Decorator $groupDecorator
     * Decorator to use for group rows
     * @return null
     */
    public function addExportGroupDecorator(Decorator $groupDecorator) {
        $this->exportGroupDecorators[] = $groupDecorator;
    }

    /**
     * Processes the search and order for the export
     * @param \ride\library\form\Form $form Form of the table
     * @return null
     */
    public function processExport(Form $form) {
        if ($this->isExportProcessed) {
            return false;
        }

        if ($form->isSubmitted()) {
            $this->processSearch($form);
            $this->processOrder($form);
        }

        $this->applySearch();
        $this->applyOrder();

        $this->isExportProcessed = true;

        return true;
    }

    /**
     * Populates the export, generates the actual export
     * @param \ride\library\html\table\export\ExportFormat $export Format of
     * the export
     * @return null
     */
    public function populateExport(ExportFormat $export) {
        if (empty($this->exportColumnDecorators)) {
            throw new TableException('No decorators set for the export view. Add a export decorator before trying to populate the export view.');
        } elseif (!$this->isExportProcessed) {
            throw new TableNotProcessedException('Table is not processed yet, call processExport() first.');
        }

        $this->addHeaderToExport($export);
        $this->addRowsToExport($export);
    }

    /**
     * Adds the header row to the export of the table based on the header
     * decorators
     * @param \ride\library\html\table\export\ExportFormat $export Format of
     * the export
     * @return null
     */
    protected function addHeaderToExport(ExportFormat $export) {
        $row = new Row();

        foreach ($this->exportColumnDecorators as $columnDecorator) {
            $cell = new HeaderCell();

            $headerDecorator = $columnDecorator->getHeaderDecorator();
            if ($headerDecorator) {
                $headerDecorator->decorate($cell, $row, 0, array());
            }

            $row->addCell($cell);
        }

        $export->addExportHeaderRow($row);
    }

    /**
     * Populates the rows of the export based on the provided values and the
     * added decorators
     * @param \ride\library\html\table\export\ExportFormat $export Format of
     * the export
     * @return null
     */
    protected function addRowsToExport(ExportFormat $export) {
        if (empty($this->values)) {
            return;
        }

        $rowNumber = 1;
        while ($value = array_shift($this->values)) {
            $this->addGroupRowToExport($export, $value, $rowNumber);
            $this->addDataRowToExport($export, $value, $rowNumber);

            $rowNumber++;
        }
    }

    /**
     * Adds a group row to the export of the table if necessairy, group
     * decorators should return a boolean to set whether to add the group row or not
     * @param \ride\library\html\table\export\ExportFormat $export Format of
     * the export
     * @param mixed $value Value of the current row
     * @param integer $rowNumber Number of the current row
     * @return null
     */
    protected function addGroupRowToExport(ExportFormat $export, $value, $rowNumber) {
        if (!$this->exportGroupDecorators) {
            return;
        }

        $row = new Row();
        $addRow = false;

        $neededCells = max(count($this->exportColumnDecorators), count($this->exportGroupDecorators));

        $numCells = 0;
        foreach ($this->exportGroupDecorators as $groupDecorator) {
            $cell = new Cell();
            $cell->setValue($value);

            $result = $groupDecorator->decorate($cell, $row, $rowNumber, $this->values);
            if ($result) {
                $addRow = true;
            }

            $row->addCell($cell);
            $numCells++;
        }

        for ($i = $numCells; $i < $neededCells; $i++) {
            $row->addCell(new Cell());
        }

        if ($addRow) {
            $exportView->addExportDataRow($row, true);
        }
    }

    /**
     * Adds a data row to the export of the table
     * @param \ride\library\html\table\export\ExportView $exportView View of
     * the export
     * @param mixed $value Value to decorate and add as table row
     * @param integer $rowNumber Number of the current row
     * @return null
     */
    protected function addDataRowToExport(ExportFormat $export, $value, $rowNumber) {
        $row = new Row();

        foreach ($this->exportColumnDecorators as $columnDecorator) {
            $cell = new Cell();
            $cell->setValue($value);

            $valueDecorator = $columnDecorator->getValueDecorator();
            $valueDecorator->decorate($cell, $row, $rowNumber, $this->values);

            $row->addCell($cell);
        }

        $export->addExportDataRow($row, false);
    }

    /**
     * Gets the HTML of this table.
     * @return string
     */
    protected function getHtmlContent() {
        if (!$this->isProcessed) {
            throw new TableNotProcessedException('Table is not processed yet, call processForm() first.');
        }

        return parent::getHtmlContent();
    }

    /**
     * Processes and applies the actions, search, order and pagination of this
     * table
     * @param \ride\library\form $form $form Form of the table
     * @return null
     */
    public function processForm(Form $form) {
        if ($this->isProcessed) {
            return false;
        }

        if ($form->isSubmitted()) {
            $data = $form->getData();

            if (isset($data[self::FIELD_NAME]) && $data[self::FIELD_NAME] == $this->getId()) {
                $this->processAction($form);
                $this->processSearch($form);
                $this->processOrder($form);
                $this->processPagination($form);
            }
        }

        $this->applySearch();
        $this->applyOrder();
        $this->applyPagination();

        $this->isProcessed = true;

        return true;
    }

    /**
     * Applies the search query to the values in this table
     * @return null
     */
    protected function applySearch() {

    }

    /**
     * Applies the order method to the values in this table
     * @return boolean True when the values have been ordered, false otherwise
     */
    protected function applyOrder() {
        if (!isset($this->orderMethods[$this->orderMethod])) {
            return false;
        }

        if ($this->orderDirection === self::ORDER_DIRECTION_ASC) {
            $this->values = $this->orderMethods[$this->orderMethod]->invokeAscending($this->values);
        } else {
            $this->values = $this->orderMethods[$this->orderMethod]->invokeDescending($this->values);
        }

        return true;
    }

    /**
     * Applies the pagination to the values in this table
     * @return null
     */
    protected function applyPagination() {
        $this->countRows = count($this->values);

        if (!$this->pageRows) {
            return;
        }

        $this->pages = ceil($this->countRows / $this->pageRows);
        if ($this->page > $this->pages || $this->page < 1) {
            $this->page = 1;
        }

        $offset = ($this->page - 1) * $this->pageRows;

        $this->values = array_slice($this->values, $offset, $this->pageRows, true);
    }

    /**
     * Processes and invokes the action if provided and submitted
     * @param \ride\library\form\Form $form Form of the table
     * @return null
     */
    protected function processAction(Form $form) {
        if (!$this->hasActions()) {
            return;
        }

        $data = $form->getData();

        $action = $data[self::FIELD_ACTION];
        if (!isset($this->actions[$action])) {
            return;
        }

        $this->actions[$action]->invoke($data[self::FIELD_ID]);

        $data[self::FIELD_ACTION] = 0;

        $form->setData($data);
    }

    /**
     * Gets the search query from the form and sets it to this table
     * @param \ride\library\form\Form $form Form of the table
     * @return null
     */
    protected function processSearch(Form $form) {
        if (!$this->hasSearch() || !$form->hasRow(self::FIELD_SEARCH_QUERY)) {
            return;
        }

        $data = $form->getData();
        if (!isset($data[self::FIELD_SEARCH_QUERY])) {
            return;
        }

        $searchQuery = trim($data[self::FIELD_SEARCH_QUERY]);

        if ($searchQuery != $this->searchQuery && $this->pageRows) {
            $this->setPage(1);
        }

        $this->searchQuery = $searchQuery;
    }

    /**
     * Gets the order from the form and sets it to this table
     * @param \ride\library\form\Form $form Form of the table
     * @return null
     */
    protected function processOrder(Form $form) {
        if (!$form->hasRow(self::FIELD_ORDER_METHOD)) {
            return;
        }

        $data = $form->getData();

        if (isset($data[self::FIELD_ORDER_METHOD])) {
            $this->setOrderMethod($data[self::FIELD_ORDER_METHOD]);
        }
    }

    /**
     * Gets the number of rows per page from the form and sets it to this table
     * @param \ride\library\form\Form $form Form of the table
     * @return null
     */
    protected function processPagination(Form $form) {
        if (!$form->hasRow(self::FIELD_PAGE_ROWS)) {
            return;
        }

        $data = $form->getData();

        if (isset($data[self::FIELD_PAGE_ROWS])) {
            $this->setRowsPerPage($data[self::FIELD_PAGE_ROWS]);
        }
    }

    /**
     * Gets the name of this form component
     * @return string
     */
    public function getName() {
        return $this->getAttribute('id', 'form-table');
    }

    /**
     * Gets the data type for the data of this form component
     * @return string|null A string for a data class, null for an array
    */
    public function getDataType() {
        return null;
    }

    /**
     * Parse the data to form values for the component rows
     * @param mixed $data
     * @return array $data
    */
    public function parseSetData($data) {
        return $data;
    }

    /**
     * Parse the form values to data of the component
     * @param array $data
     * @return mixed $data
    */
    public function parseGetData(array $data) {
        return $data;
    }

    /**
     * Prepares the form by adding field definitions
     * @param \ride\library\form\FormBuilder $builder
     * @param array $options
     * @return null
    */
    public function prepareForm(FormBuilder $builder, array $options) {
        $builder->addRow(self::FIELD_NAME, 'hidden', array(
        	'default' => $this->getId(),
        ));

        if ($this->hasActions()) {
            $options = $this->getOptionsFromKeys($this->actions);

            array_unshift($options, '---');

            $builder->addRow(self::FIELD_ACTION, 'select', array(
            	'options' => $options,
            ));
            $builder->addRow(self::FIELD_ID, 'option', array(
            	'options' => $this->values,
            	'multiple' => true,
            ));
        }

        if ($this->hasSearch()) {
            $builder->addRow(self::FIELD_SEARCH_QUERY, 'string', array(
            	'default' => $this->searchQuery,
            ));
        }

        if ($this->hasOrderMethods()) {
            $options = $this->getOptionsFromKeys($this->orderMethods);

            $builder->addRow(self::FIELD_ORDER_METHOD, 'select', array(
            	'default' => $this->orderMethod,
            	'options' => $options,
            ));
        }

        if ($this->paginationOptions) {
            $builder->addRow(self::FIELD_PAGE_ROWS, 'select', array(
            	'default' => $this->pageRows,
            	'options' => $this->paginationOptions,
            ));
        }
    }

    /**
     * Gets the keys of an array
     * @param array $list Array with options
     * @return array Array with the key of the provided list as key and value
     */
    protected function getOptionsFromKeys(array $list) {
        $options = array();

        foreach ($list as $key => $value) {
            $options[$key] = $key;
        }

        return $options;
    }

}
