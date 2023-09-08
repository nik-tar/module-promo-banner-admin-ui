<?php

namespace Niktar\PromoBannerAdminUi\Ui\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Niktar\PromoBanner\Model\Banner\GroupsResolver;

class GroupCode implements OptionSourceInterface
{
    /**
     * @var GroupsResolver
     */
    private $groupsResolver;
    /**
     * @var array
     */
    private $options;

    /**
     * @param GroupsResolver $groupsResolver
     */
    public function __construct(
        GroupsResolver $groupsResolver
    ) {
        $this->groupsResolver = $groupsResolver;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $options = [];

        foreach ($this->groupsResolver->getGroups() as $code => $name) {
            $options[] = [
                'label' => $name,
                'value' => $code
            ];
        }

        $this->options = $options;
        return $this->options;
    }
}
