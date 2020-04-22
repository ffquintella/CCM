using System;
namespace ClientBL.Tools
{
    public static class RandomGenerator
    {
        public static string GetRandomString(int lenght, bool includeSpecial = false){
           
            var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

            if (includeSpecial) chars = chars + "!@#$%&*()~?/\\,.;:";

            var stringChars = new char[lenght];
            var random = new Random();

            for (int i = 0; i < stringChars.Length; i++)
            {
                stringChars[i] = chars[random.Next(chars.Length)];
            }

            var finalString = new String(stringChars);

            return finalString;
        }
    }
}
