using System;
using System.Linq;

namespace CCM_API.Helpers
{
    public static class RandomHelper
    {
        private static Random random = new Random();
        public static string RandomString(int length, bool onlyUpper = false)
        {
            string chars;
            chars = onlyUpper ? "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789" : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvxyz0123456789";
            return new string(Enumerable.Repeat(chars, length)
                .Select(s => s[random.Next(s.Length)]).ToArray());
        }
    }
}