using NGettext;
using System;
using System.Globalization;

namespace CCM_WebClient.Translation
{
    internal class T
    {
        private static readonly ICatalog _Catalog = new Catalog("Geral", "./Locale", new CultureInfo("pt-BR"));
        
        public static string _(string text)
        {
            return _Catalog.GetString(text);
        }

        public static string _(string text, params object[] args)
        {
            return _Catalog.GetString(text, args);
        }

        public static string _n(string text, string pluralText, long n)
        {
            return _Catalog.GetPluralString(text, pluralText, n);
        }
        
        public static string _f(string format, params string[] texts)
        {
            for (int i = 0; i < texts.Length; i++)
            {
                format = format.Replace("{"+i.ToString() +"}", _Catalog.GetString(texts[i]) );
            }

            return format;
        }

        public static string _n(string text, string pluralText, long n, params object[] args)
        {
            return _Catalog.GetPluralString(text, pluralText, n, args);
        }

        public static string _p(string context, string text)
        {
            return _Catalog.GetParticularString(context, text);
        }

        public static string _p(string context, string text, params object[] args)
        {
            return _Catalog.GetParticularString(context, text, args);
        }

        public static string _pn(string context, string text, string pluralText, long n)
        {
            return _Catalog.GetParticularPluralString(context, text, pluralText, n);
        }

        public static string _pn(string context, string text, string pluralText, long n, params object[] args)
        {
            return _Catalog.GetParticularPluralString(context, text, pluralText, n, args);
        }
    }
}