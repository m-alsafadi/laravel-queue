<?php

namespace MAlsafadi\LaravelQueue\Traits;

/**
 * @ mixin \MAlsafadi\LaravelQueue\LaravelQueue
 */
trait TLaravelQueueFile
{
// region: file
    public function getHistoryFilename(): string
    {
        $this->debug("get history filename", [ __METHOD__, func_get_args() ]);
        $name = $this->STORAGE_NAME_HISTORY;
        if( $this->use_cache ) {
            return str_replace_last(".json", '', $name);
        }

        return str_finish($name, ".json");
    }

    public function getFilename(?bool $is_fail = null): string
    {
        $this->debug("get filename", [ __METHOD__, func_get_args() ]);
        $name = $is_fail === false ? $this->STORAGE_NAME_FAIL : ($is_fail === true ? $this->STORAGE_NAME_SUCCESS : $this->STORAGE_NAME);
        if( $this->use_cache ) {
            return str_replace_last(".json", '', $name);
        }

        return str_finish($name, ".json");
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
     */
    public function disk()
    {
        return $this->disk ?: $this->filesystem->disk($this->config('disk', $this->configs->get('filesystems.default')));
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function getDisk(?bool $is_fail = null)
    {
        $this->debug("get disk", [ __METHOD__, func_get_args() ]);
        $disk = $this->disk;
        if( !$this->exists($is_fail) ) {
            $this->putDisk([], $is_fail);
        }

        $data = $disk->get($this->getFilename($is_fail));
        $decoded = json_decode($data ?: "[]", true);

        if( is_null($decoded) || json_last_error() !== JSON_ERROR_NONE ) {
            throw new \RuntimeException("Queue file [{$this->getFilename($is_fail)}] contains an invalid JSON structure.");
        }

        return $decoded;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function putDisk($value = null, ?bool $is_fail = null)
    {
        $this->debug("put disk", [ __METHOD__, func_get_args() ]);
        $value = value($value) ?: [];
        $flags = $this->human_readable_save ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES;
        $this->disk->put($this->getFilename($is_fail), json_encode(array_wrap($value), $flags));

        return $this;
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getHistoryDisk()
    {
        $this->debug("get history disk", [ __METHOD__, func_get_args() ]);
        $disk = $this->disk;
        if( !$this->historyExists() ) {
            $this->putHistoryDisk([]);
        }

        $data = $disk->get($this->getHistoryFilename());
        $decoded = json_decode($data ?: "[]", true);

        if( is_null($decoded) || json_last_error() !== JSON_ERROR_NONE ) {
            throw new \RuntimeException("Queue history file [{$this->getHistoryFilename()}] contains an invalid JSON structure.");
        }

        return $decoded;
    }

    /**
     * @param mixed|null $value
     *
     * @return $this
     */
    public function putHistoryDisk($value = null)
    {
        $this->debug("put history disk", [ __METHOD__, func_get_args() ]);
        $value = value($value) ?: [];
        $flags = JSON_UNESCAPED_SLASHES;
        $this->disk->put($this->getHistoryFilename(), json_encode(array_wrap($value), $flags));

        return $this;
    }

    /**
     * @return $this
     */
    public function getDataDisk()
    {
        $this->debug("get data disk", [ __METHOD__, func_get_args() ]);
        $disk = $this->disk;
        if( !$this->dataExists() ) {
            $this->putDataDisk([]);
        }

        $data = $disk->get("data.json");
        $decoded = json_decode($data ?: "[]", true);

        if( is_null($decoded) || json_last_error() !== JSON_ERROR_NONE ) {
            throw new \RuntimeException("Data file [data.json] contains an invalid JSON structure.");
        }

        return $decoded;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function putDataDisk($value = null)
    {
        $this->debug("put data disk", [ __METHOD__, func_get_args() ]);
        $value = value($value) ?: [];
        $this->disk->put("data.json", json_encode(array_wrap($value), JSON_UNESCAPED_SLASHES));

        return $this;
    }
// endregion: file
}
