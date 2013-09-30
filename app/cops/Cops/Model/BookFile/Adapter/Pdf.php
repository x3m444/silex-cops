<?php
/*
 * This file is part of Silex Cops. Licensed under WTFPL
 *
 * (c) Mathieu Duplouy <mathieu.duplouy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cops\Model\BookFile\Adapter;

use Cops\Model\BookFile\BookFileAbstract;
use Cops\Model\BookFile\BookFileInterface;

/**
 * PDF adpater model class
 *
 * @author Mathieu Duplouy <mathieu.duplouy@gmail.com>
 */
class Pdf extends BookFileAbstract implements BookFileInterface
{
    public function getFilePath()
    {
        return '';
    }
}
