<?php

namespace App\DTOs;

use Thunk\Verbs\SerializedByVerbs;
use Thunk\Verbs\Support\Normalization\NormalizeToPropertiesAndClassName;

class MoneyLogEntry extends LivewireDTO implements SerializedByVerbs
{
    use NormalizeToPropertiesAndClassName;

    public function __construct(
        public int $player_id,
        public int $round_id,
        public int $round_number,
        public string $description,
        public int $amount,
        public string $type,
        public int $balance,
    ) {
    }

    public const TYPE_BUREAUCRAT_REWARD = 'bureaucrat_reward';

    public const TYPE_PERK_REWARD = 'perk_reward';

    public const TYPE_ROUND_MODIFIER_REWARD = 'round_modifier_reward';

    public const TYPE_FREEZE = 'freeze';

    public const TYPE_HIDE = 'hide';

    public const TYPE_INCOME = 'income';

    public const TYPE_PENALIZE = 'penalize';

    public const TYPE_TREASURY = 'treausry';

    public const TYPE_SPEND = 'spend';

    public const TYPE_UNFREEZE = 'unfreeze';

    public const TYPE_WIN_AUCTION = 'win_auction';
}
