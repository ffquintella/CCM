using System.Globalization;
using NGettext;

namespace CCM_WebClient.Translation
{
    public class S
    {
        private readonly ICatalog _catalog;
        
        public S(string catalog)
        {
            _catalog = new Catalog(catalog, "./Locale", new CultureInfo("pt-BR"));
        }
        
        public string _(string text)
        {
            return _catalog.GetString(text);
        }
    }
}