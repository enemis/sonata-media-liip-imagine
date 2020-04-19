<?php


namespace Enemis\SonataMediaLiipImagineBundle\Thumbnail;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Routing\RouterInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Throwable;

class LiipImagineThumbnail implements ThumbnailInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var CacheManager;
     */
    protected $cacheManager;

    /**
     * @var array
     */
    protected $filterSets = [];

    /**
     * @param RouterInterface $router
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param array $filterSets
     */
    public function setFilterSets(array $filterSets)
    {
        $this->filterSets = $filterSets;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            $path = $provider->getReferenceImage($media);

            return $this->getCdn()->getPath($path, $media->getCdnIsFlushable());
        } else {
            if (MediaProviderInterface::FORMAT_ADMIN === $format) {
                $format = sprintf('%s_%s', $media->getContext(), $format);
            }

            $path = $this->cacheManager->getBrowserPath($provider->generatePublicUrl($media, 'reference'), $format);
        }
        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $provider->getReferenceImage($media);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(MediaProviderInterface $provider, MediaInterface $media)
    {
        // nothing to generate, as generated on demand
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null)
    {
        if (\is_string($formats)) {
            $formats[] = $formats;
        }
        if (!$formats) {
            $formats = \array_keys($provider->getFormats());
        }

        foreach ($formats as $format) {
            try {
                $referencePath = $provider->generatePublicUrl($media, 'reference');
                $this->cacheManager->remove([$referencePath], [$format]);
            } catch (Throwable $exception) {}
        }
    }
}
