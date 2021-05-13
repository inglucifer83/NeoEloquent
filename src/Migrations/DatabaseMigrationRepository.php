<?php

namespace Vinelab\NeoEloquent\Migrations;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Schema\Builder as SchemaBuilder;

class DatabaseMigrationRepository implements MigrationRepositoryInterface
{
    /**
     * The database connection resolver instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * The migration model.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Model
     */
    protected $model;

    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection;

    /**
     * @param \Illuminate\Database\ConnectionResolverInterface $resolver
     * @param \Vinelab\NeoEloquent\Schema\Builder              $schema
     * @param \Vinelab\NeoEloquent\Eloquent\Model              $model
     */
    public function __construct(ConnectionResolverInterface $resolver, SchemaBuilder $schema, Model $model)
    {
        $this->resolver = $resolver;
        $this->schema = $schema;
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getRan()
    {
        return $this->model->all()->lists('migration');
    }

    /**
     * Get list of migrations.
     *
     * @param int $steps
     *
     * @return array
     */
    public function getMigrations($steps)
    {
        $query = $this->label()->where('batch', '>=', '1');

        return $query->orderBy('migration', 'desc')->take($steps)->get()->all();
    }

    /**
     * Get the completed migrations with their batch numbers.
     *
     * @return array
     */
    public function getMigrationBatches()
    {
        $query = $this->label()->where('batch', '>=', '1');

        return $query->orderBy('migration', 'asc')->pluck('batch', 'migration')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getLast()
    {
        return $this->model->whereBatch($this->getLastBatchNumber())->get()->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function log($file, $batch)
    {
        $record = ['migration' => $file, 'batch' => $batch];

        $this->model->create($record);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($migration)
    {
        $this->model->where('migration', $migration->migration)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getNextBatchNumber()
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastBatchNumber()
    {
        return $this->label()->max('batch');
    }

    /**
     * {@inheritdoc}
     */
    public function createRepository()
    {
    }
    
    public function deleteRepository()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function repositoryExists()
    {
        return $this->schema->hasLabel($this->getLabel());
    }

    /**
     * Get a query builder for the migration node (table).
     *
     * @return \Vinelab\NeoEloquent\Query\Builder
     */
    protected function label()
    {
        return $this->getConnection()->table([$this->getLabel()]);
    }

    /**
     * Get the connection resolver instance.
     *
     * @return \Illuminate\Database\ConnectionResolverInterface
     */
    public function getConnectionResolver()
    {
        return $this->resolver;
    }

    /**
     * Resolve the database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->resolver->connection($this->connection);
    }

    /**
     * {@inheritdoc}
     */
    public function setSource($name)
    {
        $this->connection = $name;
    }

    /**
     * Set migration models label.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->model->setLabel($label);
    }

    /**
     * Get migration models label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->model->getLabel();
    }

    /**
     * Set migration model.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Model $model
     */
    public function setMigrationModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get migration model.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function getMigrationModel()
    {
        return $this->model;
    }
}
