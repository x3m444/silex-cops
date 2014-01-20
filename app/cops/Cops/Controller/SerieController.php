<?php
/*
 * This file is part of Silex Cops. Licensed under WTFPL
 *
 * (c) Mathieu Duplouy <mathieu.duplouy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cops\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Cops\Model\BookFile\BookFileFactory;
use Cops\Exception\SerieException;

/**
 * Serie controller class
 * @author Mathieu Duplouy <mathieu.duplouy@gmail.com>
 */
class SerieController
    extends \Cops\Model\Controller
    implements \Silex\ControllerProviderInterface
{
    /**
     * Connect method to dynamically add routes
     *
     * @see \Silex\ControllerProviderInterface::connect()
     *
     * @param \Application $app Application instance
     *
     * @return ControllerCollection ControllerCollection instance
     */
    public function connect(\Silex\Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->get('/download/{id}/{format}', __CLASS__.'::downloadAction')
            ->assert('id', '\d+')
            ->bind('serie_download');

        $controller->get('/list/{letter}/{page}', __CLASS__.'::listAction')
            ->assert('letter', '\w+|0')
            ->value('page', 1)
            ->bind('serie_list');

        $controller->get('/{id}', __CLASS__.'::detailAction')
            ->assert('id', '\d+')
            ->bind('serie_detail');

        return $controller;
    }

    /**
     * List series action
     *
     * @param Silex\Application $app
     * @param string|0          $letter
     *
     * @return string
     */
    public function listAction(\Silex\Application $app, $letter=0)
    {
        if ($letter === '0') {
            $letter = '#';
        }
        $series = $this->getModel('Serie')->getCollection()->getByFirstLetter($letter);

        return $app['twig']->render($app['config']->getTemplatePrefix().'serie_list.html', array(
            'letter' => $letter,
            'series' => $series,
            'pageTitle' => sprintf($app['translator']->trans('Series beginning by %s'), $letter),
        ));
    }

    /**
     * Serie detail action
     *
     * @param Silex\Application $app
     * @param id                $id
     */
    public function detailAction(\Silex\Application $app, $id)
    {
        try {
            $serie = $this->getModel('Serie')->load($id);
        } catch (SerieException $e) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        return $app['twig']->render($app['config']->getTemplatePrefix().'serie.html', array(
            'serie'     => $serie,
            'pageTitle' => $serie->getName(),
        ));
    }

    /**
     * Download all serie books as archive file
     *
     * @param Silex\Application $app
     * @param int               $id     The serie ID
     * @param string            $format The archive file format (zip|tar.gz)
     *
     * @return string
     */
    public function downloadAction(\Silex\Application $app, $id, $format)
    {
        try {
            $serie = $this->getModel('Serie')->load($id);

            $serieBooks = $this->getModel('BookFile')->getCollection()->getBySerieId($serie->getId());

        } catch (SerieException $e) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        } catch (AdapterException $e) {
            return $app->redirect(
                $app['url_generator']->generate(
                    'serie_detail',
                    array(
                        'id' => $serie->getId()
                    )
                )
            );
        }

        $archiveClass = $this->getModel('Archive\\ArchiveFactory', $format)
            ->getInstance();

        $archive = $archiveClass->addFiles($serieBooks)
            ->generateArchive();

        return $app
            ->sendFile($archive)
            ->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $serie->getDownloadSafeName().$archiveClass->getExtension()
            );
    }

}
