using System;
namespace ClientBL.Tools
{
    public static class FormatConversion
    {

        public static DateTime UnixTimeStampToDateTime(String unixTimeStamp)
        {
            return UnixTimeStampToDateTime(Double.Parse(unixTimeStamp));
        }

        public static DateTime UnixTimeStampToDateTime(double unixTimeStamp)
        {
            // Unix timestamp is seconds past epoch
            System.DateTime dtDateTime = new DateTime(1970, 1, 1, 0, 0, 0, 0, System.DateTimeKind.Utc);
            dtDateTime = dtDateTime.AddSeconds(unixTimeStamp).ToLocalTime();
            return dtDateTime;
        }
    }
}
