<?php

namespace App\Repositories;

use App\Entities\DatabaseResult;
use App\Models\Model;
use Config\Database;
use Exception\DuplicatedValueException;
use Exception\IndexNotFoundException;

abstract class Repository implements RepositoryInterface
{
    /**
     * Classname of the repository model.
     *
     * @var string
     */
    protected string $model;

    /**
     * Name of the database table. Is the name of the model in plural. For example if Model's name is
     * 'User' the database table's name will be 'users'
     *
     * @var string|null
     */
    protected ?string $table;

    /**
     * Query to be executed .
     *
     * @var string
     */
    protected string $query;

    /**
     * Params from query statement.
     *
     * @var array|null
     */
    protected ?array $params;

    /**
     * @param string $model - Full classname of a Model class.
     */
    protected function __construct(string $model)
    {
        $this->model = $model;
        $class = explode('\\', $model);
        $className = strtolower(end($class));
        $this->table = $className . 's';
    }

    /**
     * Prepare a query to retrieve all entities of from the database.
     *
     * @return array
     */
    public function findAll(): array
    {
        $this->query = 'SELECT * FROM ' . $this->table;
        return $this->result()->getData();
    }

    /**
     * Retrieve the entity assoc to the given id.
     *
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        return $this->findAllBy(['id' => $id])->result()->getData();
    }

    /**
     * Prepare a query to retrieve all entities from the database only with the data of the selected columns.
     *
     * @param string ...$columns - String with the name of the column. Could be more than one parameter.
     * By default, is '*'
     * @return $this
     */
    protected function find(string ...$columns): Repository
    {
        if (!sizeof($columns))
            $columns[] = '*';

        $this->query = "SELECT " . implode(', ', $columns) . " FROM " . $this->table;
        return $this;
    }

    /**
     * Prepare a query to retrieve all entities from the database with the conditions to each column.
     * The conditions will be interpreted as: column *equals to* condition.
     *
     * @param array $columns - It must be in the format ['column' => 'condition'].
     * E.g: ['id' => 2] or ['name' => 'John'].
     * @return $this
     */
    protected function findAllBy(array $columns): Repository
    {
        $selectedColumns = array_keys($columns);
        $this->query = "SELECT * FROM " . $this->table . " WHERE ";

        $params = array();
        foreach ($selectedColumns as $column) {
            $params[] = $column . " = " . "'" . $columns[$column] . "'";
        }

        $this->query .= implode('AND ', $params);
        return $this;
    }

    /**
     * Prepare a query to retrieve all entities from the database only with the data of the selected columns
     * and the conditions to each column. The conditions will be interpreted as: column *equals to* condition.
     *
     * @param array $columns - It must be in the format ['column' => 'condition'].
     * E.g: ['id' => 2] or ['name' => 'John'].
     * @return $this
     */
    protected function findBy(array $columns): Repository
    {
        $selectedColumns = array_keys($columns);
        $this->query = "SELECT " . implode(', ', $selectedColumns) . " FROM " . $this->table . " WHERE ";

        $params = array();
        foreach ($selectedColumns as $column) {
            $params[] = $column . " = ?" . $columns[$column];
        }

        $this->query .= implode('AND ', $params);
        return $this;
    }

    /**
     * Add a **where** clause to the query.
     *
     * @param array|string $conditions - Could be an array of conditions or a custom string.
     * 1. The array must be in the format ['column' => 'condition'].
     * E.g: ['id' => 2] or ['name' => 'John']. The conditions will be interpreted as: column equals to condition.
     *
     * 2. The string can contain any type of conditions.
     *
     * @return $this
     */
    protected function where(array|string $conditions): Repository
    {
        $this->query .= " WHERE ";
        if (is_array($conditions)) {
            $selectedColumns = array_keys($conditions);
            $params = array();
            foreach ($selectedColumns as $column) {
                $params[] = $column . " = " . "'" . $conditions[$column] . "'";
            }

            $this->query .= implode(' AND ', $params);
        } else {
            $this->query .= $conditions;
        }
        return $this;
    }

    /**
     * Adds Joins sentence to the query.
     * The sentence will be created as: JOIN $table1 ON $table1.columnTable1 = $table2.columnTable2
     *
     * @param string $table1
     * @param string $table2
     * @param string $columnTable1
     * @param string $columnTable2
     * @return $this
     */
    protected function join(string $table1, string $table2, string $columnTable1, string $columnTable2): Repository
    {
        $this->query .= " JOIN " . $table1 . " ON " . $table1 . "." . $columnTable1 . " = " . $table2 . "." . $columnTable2;
        return $this;
    }

    /**
     * Execute an insert statement with all properties contained in the given Model.
     *
     * @param Model $model
     * @return Model The inserted model.
     * @throws DuplicatedValueException
     */
    public function save(Model $model): Model
    {
        $values = $model->__dataAttributes();

        /*+--------------------------------------------------------------------+
        * | Agrega comillas simples a cada uno de los valores de los atributos |
        * +--------------------------------------------------------------------+*/
        $values = array_map(fn($value) => "'" . $value . "'" ?? "null", $values);

        $columns = array_keys($values);

        $this->query = "INSERT INTO " . $this->table;
        $this->query .= '(' . implode(', ', $columns) . ') ';
        $this->query .= ' VALUES (' . implode(', ', $values) . ')';

        $result = $this->result();
        if (!is_null($result->getError())) {
            $e = $result->getError();
            if ($e->getCode() == 23000) {
                throw new DuplicatedValueException($e->getMessage());
            }
        }

        $lastId = $result->getLastInsertId();

        return $this->findById($lastId);
    }

    /**
     * Execute an update statement with the properties of the given model
     *
     * @throws DuplicatedValueException
     * @throws IndexNotFoundException
     */
    public function update(Model $model): Model
    {
        $values = $model->__dataAttributes();
        $id = $values['id'];

        /*+--------------------------------------------------------------------+
        * | Agrega comillas simples a cada uno de los valores de los atributos |
        * +--------------------------------------------------------------------+*/
        $values = array_map(fn($value) => "'" . $value . "'" ?? "null", $values);
        $columns = array_keys($values);

        $this->query = "UPDATE " . $this->table;

        $sets = array();
        foreach ($columns as $column) {
            $sets[] = $column . " = " . $values[$column];
        }

        $this->query .= " SET " . implode(', ', $sets);
        $this->query .= " WHERE id = " . $id;
        
        $result = $this->result();
        if (!is_null($result->getError())) {
            $e = $result->getError();
            if ($e->getCode() == 23000) {
                throw new DuplicatedValueException($e->getMessage());
            }
        }

        if ($result->getRowsAffected() == 0) {
            throw new IndexNotFoundException();
        }

        return $model;
    }

    /**
     * Execute a custom query. It can contain any number of params. The result is without model response.
     *
     * @param string $query - Parameters must be represented by '?' symbol.
     * e.g: 'SELECT * FROM users WHERE id = ? AND email = ?'
     * @param ...$params - Variables in order to assoc with each parameter.
     * @return DatabaseResult If error return false
     */
    protected function customQuery(string $query, ...$params): DatabaseResult
    {
        $this->query = $query;
        $this->params = $params;

        return $this->result(false, true);
    }

    /**
     * Execute the prepared query.
     *
     * @param bool $modelResponse - true by default
     * @param bool $hasParams - false by default
     * @return DatabaseResult If error return false. If all was well without result return null.
     */
    protected function result(bool $modelResponse = true, bool $hasParams = false): DatabaseResult
    {
        if (!$hasParams)
            $this->params = array();
        if ($modelResponse)
            $result = Database::execute($this->query, $this->model, $this->params);
        else
            $result = Database::execute($this->query, null, $this->params);

        return $result;
    }
}