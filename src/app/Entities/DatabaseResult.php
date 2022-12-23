<?php

namespace App\Entities;

use App\Models\Model;
use Exception;
use stdClass;

class DatabaseResult
{
    private ?bool $status = null;
    private ?int $rowsAffected = null;
    private ?Exception $error = null;
    private ?int $lastInsertId = null;
    private Model|stdClass|array|null $data;

    /**
     * @return bool|null
     */
    public function getStatus(): ?bool
    {
        return $this->status;
    }

    /**
     * @param bool|null $status
     */
    public function setStatus(?bool $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int|null
     */
    public function getRowsAffected(): ?int
    {
        return $this->rowsAffected;
    }

    /**
     * @param int|null $rowsAffected
     */
    public function setRowsAffected(?int $rowsAffected): void
    {
        $this->rowsAffected = $rowsAffected;
    }

    /**
     * @return Exception|null
     */
    public function getError(): ?Exception
    {
        return $this->error;
    }

    /**
     * @param Exception|null $error
     */
    public function setError(?Exception $error): void
    {
        $this->error = $error;
    }

    /**
     * @return int|null
     */
    public function getLastInsertId(): ?int
    {
        return $this->lastInsertId;
    }

    /**
     * @param int|null $lastInsertId
     */
    public function setLastInsertId(?int $lastInsertId): void
    {
        $this->lastInsertId = $lastInsertId;
    }

    /**
     * @return Model|array|stdClass|null
     */
    public function getData(): array|stdClass|Model|null
    {
        return $this->data;
    }

    /**
     * @param Model|array|stdClass|null $data
     */
    public function setData(array|stdClass|Model|null $data): void
    {
        $this->data = $data;
    }


}