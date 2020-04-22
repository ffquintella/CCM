using System;

namespace CCM_API.Helpers
{
    public static class StringHelper
    {
        public static string ConvertToBase64(string text)
        {
            byte[] encodedBytes = System.Text.Encoding.UTF8.GetBytes(text);
            return ConvertToBase64(encodedBytes);
        }
        
        public static string ConvertToBase64(byte[] encodedBytes)
        {
            string encodedTxt = Convert.ToBase64String(encodedBytes);
            return encodedTxt;
        }
        
        public static string ConvertFomBase64(string text)
        {
            byte[] decodedBytes = Convert.FromBase64String(text);
            string decodedTxt = System.Text.Encoding.UTF8.GetString(decodedBytes);
            return decodedTxt;
        }
    }
}