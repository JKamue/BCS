<?php


class Lineup
{
    private $id;
    private $clan;
    private $member1;
    private $member2;
    private $member3;
    private $member4;

    public static function createFromArray($clan, $members) : Lineup
    {
        return self::create($clan,  $members[0], $members[1], $members[2], $members[3]);
    }

    public static function create(Clan $clan, Member $member1, Member $member2, Member $member3, Member $member4) : Lineup
    {

    }

    private function __construct(Clan $clan, Member $member1, Member $member2, Member $member3, Member $member4)
    {
        $this->clan = $clan;
        $this->member1 = $member1;
        $this->member2 = $member2;
        $this->member3 = $member3;
        $this->member4 = $member4;
        $this->id = $this->calcId();

    }

    private function calcId() : String
    {
        return md5(
            $this->clan->id().
            $this->member1->id().
            $this->member2->id().
            $this->member3->id().
            $this->member4->id()
        );
    }
}