<?php
namespace Tests\Traits;

/**
 * Trait DatabaseAssertions
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Traits
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
trait DatabaseAssertions
{
    /**
     * Assert that a given where condition exists in the database.
     *
     * @param  string $table
     * @param  array $data
     * @param  string|null $onConnection
     */
    public function assertInDatabase(string $table, array $data, $onConnection = null)
    {
        $this->seeInDatabase($table, $this->castData($data), $onConnection);
    }


    /**
     * Cast data to its right MySQL values
     *
     * @param array $data
     *
     * @return array
     */
    public function castData(array $data)
    {
        foreach ($data as $field => $value) {
            if ($value === true) {
                $data[$field] = 1;
            }
            if ($value === false) {
                $data[$field] = 0;
            }
            if ($value instanceof \DateTime) {
                $data[$field] = $value->format("Y-m-d H:i:s");
            }
        }

        return $data;
    }


    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param  string $table
     * @param  array $data
     * @param  string|null $onConnection
     */
    public function assertNotInDatabase(string $table, array $data, $onConnection = null)
    {
        $this->notSeeInDatabase($table, $this->castData($data), $onConnection);
    }


    /**
     * Assert that an event is logged in the database
     *
     * @param string $event The event type
     * @param User|int|null $user The user as object, the user_id or null if not relying on user
     * @param array $data The additional data to filter the event (associative or Eloquent mode)
     */
    public function assertLogged(string $event, $user = null, array $data = [])
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[] = [$key, $value];
                unset($data[$key]);
            }
        }

        $data = array_merge([
            ["event", $event],
            ["user_id", $user === null || is_int($user) ? $user : $user->id]
        ], $data);

        $count = Log::where($data)
            ->whereBetween("created_at", [Carbon::now()->subSeconds(10), Carbon::now()])// 10 seconds diff
            ->count();

        $this->assertGreaterThan(0, $count, sprintf(
            "Unable to find row in database table [logs] that matched attributes [%s].", json_encode($data)
        ));
    }
}
