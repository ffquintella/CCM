<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 19/12/16
 * Time: 11:55
 */

namespace gcc;

require_once ROOT . "/interfaces/isharedStorage.interface.php";
require_once ROOT . "/class/logFactory.class.php";

class mockSharedStorage implements isharedStorage
{


    private $log;


    public function __construct($expirationTime = AUTH_TOKEN_TIME)
    {
        $this->log = logFactory::getLogger();
    }

    public function ping():bool
    {
        return true;
    }

    public function get(string $key)
    {
        $sec = new Secure();

        switch ($key) {
            case 'app:tapp':
                return "1TOLxg8YvrEwgBa3rAFEneDgMaO8AQc39wuASY550ggBkcsAERu31Ajv0qvAEzF3OQxBKH9QHb5BVQ2HpPgQYBtOSg1+lDawGiNHDA8XdH/QHY2bKgsjjVcQ6GINRwpqpWwwYIJ78A7Ukigw8/B6BQceo1oQFS8LEQTHeCwAlAKjYwHUaOlAGcVchQEE6DXgUKowdgppYCbQiRSaPQMAaR8Ah9sodwbuOTsQWVDPKg/18K/Q8/B6BQceo1oQ9OhuvQuq46iwpc0rvwdB2z/gqQjSBQzU4oLAUU7cwwTF68rw0LdatQkLAl1ARmn/xwc1MxKwx5rEfw96qyWwPZM17g11gbsgBhUvwQr1NN2QWpHHyAJbYvowBkKzcgOGv4+QtKeAoAAbl3fAXSmFjg8Zs9bwibxhuAxH6KlAHDVEzAvtrRiQak1a8QHVHeRw5uo/QAULvCgguBnRfgrdUZBwQgNWOQKFSURAzMKyLg4AENmw2u6zawBtnWswaKqAhwgR6lZwi455Hgx+JyXwKZDzDw3pKr1QSBkkeQoiTjjgj08T7AcgMEjQSsGbqAAZyeuA1+TLNAMmo1/QkpERlgSF8pUAD9GYhw0pxhCwiiVkdQw+TBcgObgvKA9nk7kg+8XLewSMuLJQ2MJkLgFduReg1sr0kQEhnQsAqsejKQ2zPtVwWLwjKgAZc4gA3DSotQWjYinAou5ZPQdLwWVgy+fQWAetZjkQmj3oXQfFJ0Rw12iG7AU5MmXQ3aNzyws78U8wxW8ydQKVtgbA";
                break;
            case 'app:tapp2':
                return "1TOLxg8YvrEwgBa3rAFEneDgMaO8AQc39wuASY550ggBkcsAERu31Ajv0qvAfj6WJg+T/Oawb5J/DAQw99kAC/dVdgulgTiAwMrdDgPbMf4g+W64lg3eZotA/+mGdAblsoogYeQ17Qe9wDqQuOuOig0Nt9egJ7afHA9C3GMAhXTFTQXUt2Uw9fD5TQVegh7Q9s4kRAWqmvIQI3mwWQNMj7rgpTmsdghzg3RAKNZ0KQ2PE22QuOuOig0Nt9egnViu/QJczNJQ/+HfrwQ9Y3NQ4+FGLgneWaXwZ8lLOw10Tx9QhLXBfQYSGpkwmqd9hgE/eHzwVUZ9DAwjItHAwRy5DAKTH0sQfXjofQnQOuKguBnRfgrdUZBweJOmQAXWfPkQT2LfmgtrkGxQSsGbqAAZyeuAN2hanAL6pB1QMBNiawL6j8Cw1Uj1yA+gg+Awi7g4ewL/SjHQ9LFpYgLbJDewIFIyNANBeinwWMe/tgLjoICArEquTAVR91eAXqqYtQm4xutAtz1AngMviXzgdW337gQAUHBArkpriAejxREA3+5K4w21ZnzgbCeIBQcHIPDwVqQhqQdrszXQ40pbeQnfCP8wA2SSzAhbsR/AXf8EJQX/ZsXwyU+CbwWPOq5QR2FO7A/YEefgYA8YOQbAk4MQ34nTQwbeKn1AWpHHyAJbYvowdUGZGQ5eWLBAHbPvGwPt/f9QV8H4Eg4Uiu8wWyk83Qm/5unQ9o9w2Av110XgxYqWQg4HpTjAUfOacAk+DKvgSEP44Atkr8oA34nTQwbeKn1AWpHHyAJbYvowlLeh0gjE4TDAZNJywQEAauVQTJ2IUARdqH7QabA9eQpPNN0Q9NAMrg8ZJfawuUEWZQNLDcZwB5A8Yw6tvxKQOAgcjAToIaTA4hoJHQJyxg9QL3xJsgZtd1Og+ZiHLwR9a/yQC/VXlwRtNJCg16m8xghZ3k8g5DRYSglC/i9Ax5rEfw96qyWwPZM17g11gbsgBhUvwQr1NN2QWpHHyAJbYvowBkKzcgOGv4+QtKeAoAAbl3fAXSmFjg8Zs9bwibxhuAxH6KlAHDVEzAvtrRiQqCx7NQH8/3WQ9t7jtgfbYwKwlagUKQjXgu8QIfGG4AI/XiIwklb3HQt1hWEwh7/+JAtNOCLgDF6UQgmU5p+AHcXwLQ7zHUwQ6evQlA1VY8Awb7XHHAf3JewQn784aQSNgM2APNWkDggBOcbg4Hbi/Q9Vwdsg6aSoxgZ4uOfwEJM+cgXVMg8ACArDTwAn4IsAm6bdRANpmunAUwuE+QLHpk3g/C21CAD2ruFw8hX3TwEOorgAoeZObQ1lhDqAldqP0QisfG3AoKK2Zw8ahybA7w+scwNhFhkQtYF3CwAG+R3Axehxew+D6x8QMNv4Pgai9/jwmhONwQoMcX3QWyk83Qm/5unQ9o9w2Av110XgJO3YEwTUAY/QvVx7bA2OyCJQMvm8FAObXUqAi7BrxAPzXpEAHUPYQQ9mAjTwUCiI4Az5QgsgRUHXogYldwzwUZFtmAffXBkgQU9UuAbcoNbgTJBc1Ab4dtDwmuh8qQyGJ3jAE3zZEQXtRWEw";
                break;
            case 'user:utestes':
                $user = new userAccount('utestes', 'teste');
                $user->addPermission(array('app:tapp' => 'writer', 'admin' => true));
                return $sec->encrypt(serialize($user));

                //return "ODvLYwO3SsdQPHu9oQashoPwqb5J7wyt21Ows3bnZQmR47FQI3jRnAszORLgUtAISg9qQwZg5mqO2wHwAfZQv2ziKgk4OAkwrH4BKwU74jnQ33qt7wy/SY/AyBVRsQ77yoSQ6maLmw9RiCbQCzbNyANvBx4wg/trsgGqCSZQLk7JrAjHcQewvPyUpwNT6HMg6GINRwpqpWwweb5FUwtDgtlwVt6jQQ9tKa6Ak/LdkgLbsbBA+LYKgA1P7lXg89pIJg93NK1wTk3/1AFaIHEwi3ew5gV1LaPw8+H6/ADhMzngFzd15gbPCjPgIsCLXQzCWDiwZ2an5wmmoqBQo/Fuuw/u7IiAVgAmigmvEHpw/hDybgMW889QtcWolAeLG6xwOLFo9Q3bJrbAC0mmggujiwXgSKF1nAo4onOwMBPPpQ9cP+aQMHPqMAyNQQLgcptuXQU/otUww2r+aAN/KL2wJjat4QymGkjAilwwygM5Y2RA4Fsu6gjREVmA7BduFQzkluQQuryrSgSbO8qArmt5YgDoR7pgqX0OJQ6eKpPwDtu0VAKAe+9Qzc4A2QRVA1vQ0cw7fwhNPbSQBo6UHA0je/4w57mV3wWKeVDgvU/EIQ0CdV+wge/I2QJRKeUgOoXYwwrH9KPgJZK2xghchJhQPmsO8wSzk7jAw2r+aAN/KL2wwvHkaQURJzlAee6l5QcUkSVAJfeOMQLoIVAAcHFJOQWQ8nOgWR1I1ASm3Q0wAswLQwcYCDmQEZnWeANgDcaQo07k9g8KjYaQqX0OJQ6eKpPwmvzcmQlALsBQHZIjLgvyAAvwuYXhSAUw5JdAPWSFqgqGC8gQBmdVOQCzPxLgk/AfIQW5dGbAAaVk7QEqkYRglF7dBwdx2GZALo6hBANbM37Q17OmWA2LFfaAttcLegZHPTNQz9ZMnQJEvZKgVnsYFQxrzcXArPiTWAIxCuugqSvY1gVE/T0QmExXrQWnuldgpk3frwOu4SgQtxomRgbjau4Aswg44gLIEutwNJItGQbMI71Q+ksYlw5b/i9g1J1W3g7VDeJACCTakQvi/0kQQvudwg0urMbgR94lHgqBEhQQ6hcfMg37NrqwR/PFwAuFYgqwQ8RuGwsboMSQLMpHNQjgbwCAe8swvAvSSKhw37hiegCNzeJAiUlJCAqFVqmg";
                break;
            case 'user:utestes2':
                $user = new userAccount('utestes2', 'teste123');
                $user->addPermission(array('app:tapp' => 'writer'));
                return $sec->encrypt(serialize($user));

                //return "ODvLYwO3SsdQPHu9oQashoPwqb5J7wyt21Ows3bnZQmR47FQI3jRnAszORLgUtAISg9qQwZg5mqO2wHwAfZQi4OkUQCTL3egztaZMgePj43gcG6GWQqjQUgQURIRfA5lzCJgjbLVpAThZXoQFwR8xgk1bnVwXX4gqwFGa3nAokmpbgNExN8Qg0VtdQ4BzEIQTNWMtQceGFrwL7IVGgiSB8bQIgaptgRWQDjgUkmKqwI9R7xQxR/ulAbO0svwmdkeAwPDgxvAicwRhAam/N0gy9Mrqg9GbxgAPU017gnwcKBgjhiNhQA5j+QwLDLB8w/nMXIQj+sEgAoe5h1gopRsWgOZA4iA2ypbew2gcTFg3zScnwXF8eSA/DSzsQCkahpw4euz3QnKfBeg4xA8dwrdJ3HAcIEtrQgopMAQxrl/DwDRZN5Qmdv+9wkrbYUgRCQPUQeqCvhQMFvSbQjg8ymQP1YELglzXM0Ar/qOZguS6Z3w8X0lCQ233JSg9I74DgTqz2QA1Hey+weMcOSwqKHjhwsecyfgw9drsQegffoARAw5CAc4prhgPBOX+g2LBwbACP+0YwBcAHkQFdvkkQurKbCAKiRjeQdD2x3wWdUw5gkJ8zKAOYnwlwbUzEygKY/PSgAy65LAROnRDg9is1FAQvudwg0urMbgUW+Tiw5N+kVw6hcfMg37NrqwoDJ0sgda5EmQL2SdJwzEUUZQuJGlhwA9dYsAJqrSAAsyIaoQ3N4/iAhaZEbAVMEMggHQXywgq10kQw5FKibQFWBurgMwSRhQrPi3DQRA9GRwPBOX+g2LBwbAEdm30gq4x/gQCoX8OQ6+ZCBgqYO6rA89cNeAlwejDQpj8eRAy14XTgL2ekIwGtx3YweaDScANrlPUAbwRoWQg8BkRgXxAvFAUHjLuQOjO2AgpUd0EAdtz2QghdtncgalRC0w9lIwHQ43KX4QwERpEw+P2W2gJdnuSQO04auQAneIvQxd+dyQqsWCFgBjAm/AXGtBSweZ6eJQKbCFcAgJCqaAYVPsYggAlFZwsteTawVUYQswniPOQwDk3tsQUy1o2gB61f3gi+N4nAAYVQ/wxrl/DwDRZN5Q2596zA68oz2wfhaE9ALFPNoQAKnQzQvEkgRQg0kdpgkey44gtbTr8g0KeKZwjppSQwGX3uTAqQ1UZAYihMQg";
                break;
            case 'user:utestes3':
                $user = new userAccount('utestes3', 'teste123');
                $user->addPermission(array('app:tapp' => 'writer'));
                return $sec->encrypt(serialize($user));
                //return "ODvLYwO3SsdQPHu9oQashoPwqb5J7wyt21Ows3bnZQmR47FQI3jRnAszORLgUtAISg9qQwZg5mqO2wHwAfZQM/bMXQhq+D/wUSWBGAiE29ewzf8yQgEnHSwg/T5swwCWKIowNW8E5wSUm3DwF5LR2QWk5ZRw7aMu5AqHYRWACzbNyANvBx4wg/trsgGqCSZQLk7JrAjHcQewGgqQoAArkapg6GINRwpqpWww3afWcwjrwx6wQ49uhgqli4uwydK+yw74SOQQh5D/fg1KAz/geVMJXQ29baVgzdxRbglGdCnwkc48hw0z3dOgJj2WuAVoyQTQm/X2gg9ycvhA2UqRLg6NMA5wOPldwAj+JG3A8My00Qu2xrswRiK3UwHb/MhgchH1Vw1DNc1g9uYS0w8vem7wfks01QiNDLTwk/sOaAKL/7NA30AxCwQIHTVQ1OuOIwYXZq+Aeminng1KKbmgYmu/sgX10b8gOKlvhAoFRefguO+6Bg/VKwZALL9u4AoXSHzgITof9w2lDGtwbT/2OQRmZbwg54HLEA1ucuUwgb+2cwxBXChwlF7dBwdx2GZAjUUA3QAv1o7Qx0r6TgH4jIEAx7jfrQyhtZeQ4Us9BQe6T/MgzT5SngDmNtIQ1tJcUgE6c8GQw94+qg7i8iegPtulrwMaE1TAMFvSbQjg8ymQdvQGNwDt0EawSlHvBAioZ1agJIid/gbETzpQ2WHAYALCQPZQldvdbgvH8aXQrGu82QYcnVDA3x8JhQU2xVOwCyzgywouTVFACcsP5Q6UjM0w3gYuqwL7GHMglF7dBwdx2GZA5dILTQKtvhDgcuzPfwhefm+wqIxb7Q07SxpgDo6nVwgdvw7ga0T0KwLPSHpAjppSQwGX3uTARnXLJgN4UQTQJnM1NwXGNTDAJZK2xghchJhQGhcAFQ+o3SAwFSUyYgMytw2Qs0peygmBmCqwzPX2oQfwre7AdT2ZFwYXflaQmtnnTgn9ftGQ6X6HhAQ3HUdA3bgPbANQdrtANrpmsAZCbaLAJNuFjAZjz67A3osyeQ00TcuwoUAZYAB7Yt0gQ+xi9wp9gncwEk59EQvXPhPQqX0OJQ6eKpPwvw9fLAZ45EIAFFl3gQYMZbRgEm3M0gE9WjAQLgis6A9JYd9QQKYOCQDxMxFQOHt0tQ9H1/3QNOLmqgQgytgQd35MZwKVLVUQ";
                break;
            case 'user:utestes4':
                $user = new userAccount('utestes4', 'teste123');
                //$user->addPermission(array('app:tapp' => 'writer'));
                return $sec->encrypt(serialize($user));
                //return "ODvLYwO3SsdQPHu9oQashoPwqb5J7wyt21Ows3bnZQmR47FQI3jRnAszORLgUtAISg9qQwZg5mqO2wHwAfZQM/bMXQhq+D/wUSWBGAiE29ewF0SDTwJDA7fA/T5swwCWKIowNW8E5wSUm3DwF5LR2QWk5ZRw7aMu5AqHYRWACzbNyANvBx4wg/trsgGqCSZQLk7JrAjHcQewGgqQoAArkapg6GINRwpqpWwwd3oqeAA4sZEAQ49uhgqli4uwydK+yw74SOQQopJQywLUOJsQij3UhQiA2xYQcAlLvQAm3teAOAuCAAwqs/VwN0r/3AcTVyDga/JKDwIJ38zgheAbsAEZ9HsAa3PJuwITNpFA+nwnIwhvp7ugtcMG4AA6dFlQC/aUZAgwWZww9uYS0w8vem7wfks01QiNDLTwk/sOaAKL/7NA30AxCwQIHTVQ1OuOIwYXZq+Aeminng1KKbmgYmu/sgX10b8gOKlvhAoFRefguO+6Bg/VKwZALL9u4AoXSHzgITof9w2lDGtwbT/2OQRmZbwg54HLEA1ucuUwgb+2cwxBXChwlF7dBwdx2GZAjUUA3QAv1o7Qx0r6TgH4jIEAx7jfrQyhtZeQ4Us9BQe6T/MgzT5SngDmNtIQ1tJcUgE6c8GQw94+qg7i8iegPtulrwMaE1TAMFvSbQjg8ymQdvQGNwDt0EawSlHvBAioZ1agJIid/gbETzpQ2WHAYALCQPZQldvdbgvH8aXQrGu82QYcnVDA3x8JhQU2xVOwCyzgywouTVFACcsP5Q6UjM0w3gYuqwL7GHMglF7dBwdx2GZA5dILTQKtvhDgcuzPfwhefm+wqIxb7Q07SxpgDo6nVwgdvw7ga0T0KwLPSHpAjppSQwGX3uTARnXLJgN4UQTQJnM1NwXGNTDAJZK2xghchJhQGhcAFQ+o3SAwFSUyYgMytw2Qs0peygmBmCqwzPX2oQfwre7AdT2ZFwYXflaQmtnnTgn9ftGQ6X6HhAQ3HUdA3bgPbANQdrtANrpmsAZCbaLAJNuFjAZjz67A3osyeQ00TcuwoUAZYAB7Yt0gQ+xi9wp9gncwEk59EQvXPhPQqX0OJQ6eKpPwvw9fLAZ45EIAFFl3gQYMZbRgEm3M0gE9WjAQLgis6A9JYd9QQKYOCQDxMxFQOHt0tQ9H1/3QNOLmqgQgytgQd35MZwKVLVUQ";
                break;
            case 'list:environments':
                return "qFAdLwRU9fiQSsGbqAAZyeuAaRBWAAw4HQ8QduMh2QC+rBfwQU9UuAbcoNbgTJBc1Ab4dtDwrjFUewORf7iA1cLmLw9jMh1QttcLegZHPTNQ4HU/JQttegMweFvAFwolZHOA0vxzjQ3cIl5gGXBx0gV2Zw+w8RxODw59xcXQ66hJ4QqfUVIAeSpuvA8qzx2AqCx7NQH8/3WQ9t7jtgfbYwKwlagUKQjXgu8QIfGG4AI/XiIwklb3HQt1hWEw2mUC9gPGVn/Qspz78Q54VTVwC80W0QDzsXxA8gDeIQbXugnwTIadYQhtthmQQRXxhgWcebOwQl5rnQBgnrGgrCFN8gnqAzbgohexTwoVJN0gECBXpgzA9Hlg7GLKYgkq+MnQVXUnxw6XvKDgZNIFfgWkKOXgUFCTsgPjJODQqpUHHQGRf3BAzVQiGQAxDyFQPsNNaANXEddAJMOmTA5cOX0gfIaEGAIKPFtQuOuOig0Nt9egolk9BgTzWLKwusPmdAKERSzgzNYNBQgBOmug7lZwuQRuc5VAhLXBfQYSGpkwzVQiGQAxDyFQtEKJ4gEK6XoAegoFugbNbB8wTNkfUAy/nO2A";
                break;
            case 'server:tserver':
                return "EUCgUQvC4NFwlomrcwb/+iqQAAhzdwZSWVowFlPJwAw3sj2Ak0zVjQ1amN5AOWDVZQ/LD4PA2Br3GgBfGtZw/GGV8Qs8mTegqqTG3AGcoi6wlomrcwb/+iqQn8xDVQnbcY4wsKxKCQSg67gA7TpzngEe0DpwojFl/Q8XKWUAZwdpKwYFmmkAAeQdKwdQVHqgW9HC6QTKmN7w5hyfrAD6666A+dKq1wYw5B9AsTjnCA2SbSogJVIcmA+AnBkg8iWQCwuEoxFAD1CrrAVnitPQ";
                break;
            case 'server:tserver2':
                return "EUCgUQvC4NFwlomrcwb/+iqQAAhzdwZSWVowFlPJwAw3sj2Ak0zVjQ1amN5AOWDVZQ/LD4PA75W36Qu9pUCAHlpnnAKM2B4gDIfwlAk7anFgAeQdKwdQVHqg2xuMUwu8F+3AiVQy9ACdlpYgliqkzghBBmZQLOCHGwqvMeMQROnRDg9is1FA+ZeRLgMP75lgp2gYgwPQls1g0yrreAqp09uQt86tkAZHgAOwmSg8ZQ36PQ/Qz2YYsAiwroqQJqxZqgb5AzVQHsLQFQjag4ZAmleAyA5fuv5wJm1PFQml+iFgdFzbcQBBqGaQLBqMGwY5JaPAC5gDBwBl71oATR0j+wwWX9Yw";
                break;
            case 'server:tserver3':
                return "EUCgUQvC4NFwlomrcwb/+iqQAAhzdwZSWVowFlPJwAw3sj2Ak0zVjQ1amN5AOWDVZQ/LD4PA75W36Qu9pUCAzBduRAzYSjfgDIfwlAk7anFgAeQdKwdQVHqg2xuMUwu8F+3Ac41yyw8y45CgZXHLzg5jcp7w5DBhXAi3VKdAj9Zl0AKoNXGQB8kNvweDRGawtaHy7QqiA+ZA4s36xARJgZPw4LK1rQ1uz7Qwh3NeZQV/iOuQYaOkPAu4ZbVQJadCtwSuWMXw+poIoQMy+R1AHvuW1w16nixQcL4GIg3BKdPAyWM6dQSuzWPgiRI8pwWy8G2ATNkfUAy/nO2A";
                break;
            case 'credential:tc1':
                return 'qFAdLwRU9fiQQCLSTgAq6fIQbYjt6gHfN8fgzjksaAfURQGgwvqlkw5tP2wA5fy6qArH9LmQOWQrUQ6v2PsA44ziFgu1veoQXOQwIgz6AMGQo83Xgw3C7IsQvEh62AERt/gA2968HA+8mZ9wJDUyZwlJ3VVQE8fGFAzptV4giR4QEgONDXrQhH6XqARGFTXw++aRSAcmvqig97dn+QNGkh3QNXUjiQ00E8ugt5wQpAMBvVkAJj+xPg09qMOAvyfJCwW85NvgNXUjiQ00E8ugCj7ZbQByLKSgj1I8ug/Zrb8g7a1RlgJOc+nQcjrEDgDkvxxArEquTAVR91eAXqqYtQm4xutAG18jCQpn4XlAHxho4AAEegpQ2mUC9gPGVn/Qspz78Q54VTVwC80W0QDzsXxAeLg5NgCTVs2QeImneguziKjA';
                break;
            case 'credential:tc2':
                return 'qFAdLwRU9fiQQCLSTgAq6fIQbYjt6gHfN8fgzjksaAfURQGgwvqlkw5tP2wA5fy6qArH9LmQ00RwTAMSwb0A44ziFgu1veoQXOQwIgz6AMGQo83Xgw3C7IsQvEh62AERt/gA2968HA+8mZ9wJDUyZwlJ3VVQE8fGFAzptV4giR4QEgONDXrQhH6XqARGFTXw++aRSAcmvqig97dn+QNGkh3QNXUjiQ00E8ugt5wQpAMBvVkAJj+xPg09qMOAvyfJCwW85NvgNXUjiQ00E8ugCj7ZbQByLKSgj1I8ug/Zrb8g7a1RlgJOc+nQ5mqO2wHwAfZQrEquTAVR91eAXqqYtQm4xutAqNijgw4rcovgK2lg0gb3VtSQIPJIbgLtf38w';
                break;
            case 'configuration:tconf1':
                return 'DVAeeAGEnc3g/apOZg1wAREQ+DvxTAwzFIwwteE+BQtZ9EnQQY2+og5VQEXgwOXOowGn89BwQufyUwJhi4bQtfUGSA4Lu7dgPvXeWwSZxhZwHXjNjAM1PkuA7s3pvQ6wD7+woBv5wQSGuJdg7xM6lwoXMxaQsxaYPw5o0odwXjkcug7L0AzQSJwe4Q7nZTbAEC6UgQuz0gKw+tWOHgC1PErgS67dbAmheScwrhzmBQufGaQwLEJm3QWaV7rA40pbeQnfCP8wA2SSzAhbsR/AXf8EJQX/ZsXwI6d+MQ1gl+jwKHdH1gPLCDRA';
                break;
            case 'configuration:tconf2':
                return 'DVAeeAGEnc3g/apOZg1wAREQ+DvxTAwzFIwwwQEJbQ83ZBTAQY2+og5VQEXgwOXOowGn89BwQufyUwJhi4bQYFfzaQJpFS5APvXeWwSZxhZwHXjNjAM1PkuA7s3pvQ6wD7+woBv5wQSGuJdg7xM6lwoXMxaQsxaYPw5o0odwIpmE5QrCFokA0PHO+Q8ke5ZAxhDayw/ArmKgMZJG3ATuDjRAj1I8ug/Zrb8g7a1RlgJOc+nQcjrEDgDkvxxArEquTAVR91eAXqqYtQm4xutArGSMoACMTz9Q7Ut0FAutKQfg432iywf14bQgiraCaQwPPCjAshux+wE10SNgTylfdgRjB7YA8MtCmwG9JkFgyIUnTAe2vqHQ';
                break;
            case 'ref:key-app:a119fe0f4bcdb2e401e8878be564b75c':
                return "tapp2";
                break;
            case 'ref:key-app:7a61ad3877b2557f8dc0730d6c618785':
                return "tapp";
                break;
            default:
                return null;
                break;
        }
    }

    public function getPattern($pattern)
    {
        return null;
    }

    public function getSet(string $setName)
    {
        switch ($setName) {
            case 'index:list':
                return array('environments');
            case 'index:app':
                return array('tapp', 'tapp2');
            case 'index:user':
                return array('utestes', 'utestes2', 'utestes3', 'utestes4');
                break;
            case 'index:server':
                return array('tserver', 'tserver2', 'tserver3');
                break;
            case 'index:credential':
                return array('tc1', 'tc2');
                break;
            case 'index:configuration':
                return array('tconf1', 'tconf2');
                break;
            case 'ref:app-server:tapp2':
                return array('tserver:produção', 'tserver:desenvolvimento', 'tserver3:produção');
                break;
            case 'ref:app-credential:tapp2':
                return array('tc1', 'tc2');
                break;
            case 'ref:app-credential:tapp':
                return array('tc3');
                break;
            case 'ref:app-configuration:tapp2':
                return array('tconf1', 'tconf2');
                break;
            case 'ref:app-configuration:tapp':
                return array('tconf2');
                break;
            default:
                return null;
                break;
        }
    }


    public function set(string $key, string $value, int $expiration = -1)
    {
        return true;
    }

    public function connect()
    {
        return true;
    }


    public function getStatus()
    {
        return true;
    }

    public function replace($key, $value, $expiration = -1)
    {
        return null;
    }

    public function del($key)
    {
        return true;
    }

    public function rename(string $oldName, string $newName){
        return;
    }

    public function putSet($key, $value)
    {
        return true;
    }

    public function type(string $key):string
    {
        return "";
    }

    public function delSet($set, $value)
    {
        return true;
    }

    public function searchSet(string $setName, string $pattern): ?array
    {
        switch ($setName) {
            case 'ref:app-server:Tapp2':
                return array('Tserver:Produção', 'Tserver:Desenvolvimento');
            default:
                return null;
                break;
        }

    }
}