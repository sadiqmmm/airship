<?php
declare(strict_types=1);
namespace Airship\Cabin\Bridge\Landing;

use Airship\Cabin\Bridge\{
    Blueprint\UserAccounts,
    Landing\Proto\FileManager
};

require_once __DIR__.'/init_gear.php';

/**
 * Class MyFiles
 * @package Airship\Cabin\Bridge\Landing
 */
class MyFiles extends FileManager
{
    /**
     * @var UserAccounts
     */
    protected $users;

    /**
     * @var string
     */
    protected $userUniqueId;

    /**
     * This function is called after the dependencies have been injected by
     * AutoPilot. Think of it as a user-land constructor.
     */
    public function airshipLand()
    {
        parent::airshipLand();

        $this->users = $this->blueprint('UserAccounts');
        $userId = $this->getActiveUserId();
        $this->userUniqueId = $this->users->getUniqueId($userId);
        $this->root_dir = 'user/' . $this->userUniqueId;
        $this->files->ensureDirExists($this->root_dir);
        $this->path_middle = 'my/files';
        $this->attribution = [
            'author' => null,
            'uploaded_by' => $userId
        ];
        $this->storeLensVar('path_middle', $this->path_middle);
        $this->storeLensVar('header', 'My Files');
    }

    /**
     * @route my/files/{string}/delete
     * @param string $cabin
     */
    public function confirmDeleteFile(string $cabin = '')
    {
        $dir = $this->determinePath($cabin);
        if (!\in_array($cabin, $this->getCabinNamespaces())) {
            \Airship\redirect($this->airship_cabin_prefix);
        }
        if (empty($_GET['file'])) {
            $this->commonConfirmDeleteDir($dir, $cabin);
            return;
        }
        $this->commonConfirmDeleteFile($_GET['file'], $dir, $cabin);
    }

    /**
     * @route my/files/{string}/info
     * @param string $cabin
     */
    public function getFileInfo(string $cabin = '')
    {
        $this->files->ensureDirExists($this->root_dir, $cabin);
        $dir = $this->determinePath($cabin);
        if (empty($_GET['file'])) {
            \Airship\redirect(
                $this->airship_cabin_prefix . '/my_files/' . \urlencode($cabin),
                [
                    'dir' => $dir
                ]
            );
        }
        if (!\in_array($cabin, $this->getCabinNamespaces())) {
            \Airship\redirect($this->airship_cabin_prefix);
        }
        $this->commonGetFileInfo($_GET['file'], $dir, $cabin);
    }

    /**
     * @route my/files/{string}
     * @param string $cabin
     */
    public function index(string $cabin = '')
    {
        $this->files->ensureDirExists($this->root_dir, $cabin);
        $dir = $this->determinePath($cabin);
        if (!\in_array($cabin, $this->getCabinNamespaces())) {
            \Airship\redirect($this->airship_cabin_prefix);
        }
        $this->commonIndex($dir, $cabin);
    }

    /**
     * @route my/files/{string}/move
     * @param string $cabin
     */
    public function moveFile(string $cabin = '')
    {
        $dir = $this->determinePath($cabin);
        if (!\in_array($cabin, $this->getCabinNamespaces())) {
            \Airship\redirect($this->airship_cabin_prefix);
        }
        if (empty($_GET['file'])) {
            $this->commonMoveDir($dir, $cabin);
            return;
        }
        $this->commonMoveFile($_GET['file'], $dir, $cabin);
    }

    /**
     * @route my/files
     */
    public function selectCabin()
    {
        $this->commonSelectCabin();
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Permissions check
     *
     * @return bool
     */
    protected function permCheck(): bool
    {
        return true; // You're logged in, so this is a no-brainer.
    }
}
