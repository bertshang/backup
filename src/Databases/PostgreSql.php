<?php

namespace Bertshang\Backup\DbDumper\Databases;

use Bertshang\Backup\DbDumper\DbDumper;
use Symfony\Component\Process\Process;
use Bertshang\Backup\DbDumper\Exceptions\CannotStartDump;

class PostgreSql extends DbDumper
{
    /** @var bool */
    protected $useInserts = false;

    public function __construct()
    {
        $this->port = 5432;
    }

    /**
     * @return $this
     */
    public function useInserts()
    {
        $this->useInserts = true;

        return $this;
    }

    /**
     * Dump the contents of the database to the given file.
     *
     * @param string $dumpFile
     *
     * @throws \Bertshang\Backup\DbDumper\Exceptions\CannotStartDump
     * @throws \Bertshang\Backup\DbDumper\Exceptions\DumpFailed
     */
    public function dumpToFile(string $dumpFile)
    {
        $this->guardAgainstIncompleteCredentials();

        $command = $this->getDumpCommand($dumpFile);

        $tempFileHandle = tmpfile();
        fwrite($tempFileHandle, $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($tempFileHandle)['uri'];

        $process = new Process($command, null, $this->getEnvironmentVariablesForDumpCommand($temporaryCredentialsFile));

        if (! is_null($this->timeout)) {
            $process->setTimeout($this->timeout);
        }

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    /**
     * Get the command that should be performed to dump the database.
     *
     * @param string $dumpFile
     *
     * @return string
     */
    public function getDumpCommand(string $dumpFile): string
    {
        $command = [
            "'{$this->dumpBinaryPath}pg_dump'",
            "-U {$this->userName}",
            '-h '.($this->socket === '' ? $this->host : $this->socket),
            "-p {$this->port}",
        ];

        if ($this->useInserts) {
            $command[] = '--inserts';
        }

        foreach ($this->extraOptions as $extraOption) {
            $command[] = $extraOption;
        }

        if (! empty($this->includeTables)) {
            $command[] = '-t '.implode(' -t ', $this->includeTables);
        }

        if (! empty($this->excludeTables)) {
            $command[] = '-T '.implode(' -T ', $this->excludeTables);
        }

        return $this->echoToFile(implode(' ', $command), $dumpFile);
    }

    public function getContentsOfCredentialsFile(): string
    {
        $contents = [
            $this->host,
            $this->port,
            $this->dbName,
            $this->userName,
            $this->password,
        ];

        return implode(':', $contents);
    }

    protected function guardAgainstIncompleteCredentials()
    {
        foreach (['userName', 'dbName', 'host'] as $requiredProperty) {
            if (empty($this->$requiredProperty)) {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }

    protected function getEnvironmentVariablesForDumpCommand(string $temporaryCredentialsFile): array
    {
        return [
            'PGPASSFILE' => $temporaryCredentialsFile,
            'PGDATABASE' => $this->dbName,
        ];
    }
}
