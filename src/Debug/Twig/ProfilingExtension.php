<?php

namespace SimpleMVC\Debug\Twig;

use SimpleMVC\Debug\Collector\TwigCollector;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;

class ProfilingExtension extends AbstractExtension
{
    private Profile $profile;

    public function __construct()
    {
        $this->profile = new Profile();
    }

    public function getNodeVisitors(): array
    {
        return [new ProfilingNodeVisitor(get_class($this))];
    }

    public function enter(Profile $profile): void
    {
        if ($profile->getTemplate()) {
            TwigCollector::startRender($profile->getTemplate(), []);
        }
    }

    public function leave(Profile $profile): void
    {
        if ($profile->getTemplate()) {
            TwigCollector::endRender($profile->getTemplate());
        }
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }
}