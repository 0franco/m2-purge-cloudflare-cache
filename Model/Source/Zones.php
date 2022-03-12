<?php
declare(strict_types=1);

namespace OH\Cloudflare\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;
use OH\Cloudflare\Model\Client;

/**
 * Class Zones
 * @package OH\Cloudflare\Model\Source
 */
class Zones extends AbstractSource implements OptionSourceInterface
{
    /**
     * @var Client
     */
    private $client;

    private $options;

    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    public function getAllOptions()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $zones = $this->client->getZones();

        foreach ($zones as $zone) {
            $this->options[] = [
                'label' => $zone['name'],
                'value' => $zone['id'],
            ];
        }

        return $this->options;
    }
}
